const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

class FakeClock {
    constructor() {
        this.now = 0;
        this.nextId = 1;
        this.tasks = new Map();
    }
    schedule(callback, delay, repeat = 0) {
        const id = this.nextId++;
        this.tasks.set(id, { id, callback, at: this.now + Math.max(0, Number(delay) || 0), repeat });
        return id;
    }
    clear(id) {
        this.tasks.delete(id);
    }
    async advanceTo(target) {
        while (true) {
            const next = [...this.tasks.values()].filter(task => task.at <= target).sort((a, b) => a.at - b.at || a.id - b.id)[0];
            if (!next) break;
            this.now = next.at;
            if (next.repeat) next.at += next.repeat;
            else this.tasks.delete(next.id);
            next.callback();
            await flushPromises();
        }
        this.now = target;
        await flushPromises();
    }
}

class Element {
    constructor() {
        this.style = {};
        this.listeners = {};
        this.textContent = '';
        this.disabled = false;
        this.innerHTML = '';
    }
    addEventListener(name, callback) {
        (this.listeners[name] ||= []).push(callback);
    }
    fire(name, event = {}) {
        for (const callback of this.listeners[name] || []) callback.call(this, { target: this, ...event });
    }
}

class SharedStorage {
    constructor() {
        this.values = new Map();
        this.listeners = new Map();
    }
    connect(id, listener) {
        this.listeners.set(id, listener);
    }
    set(source, key, value) {
        this.values.set(key, String(value));
        for (const [id, listener] of this.listeners) {
            if (id !== source) listener({ key, newValue: String(value) });
        }
    }
    remove(source, key) {
        this.values.delete(key);
        for (const [id, listener] of this.listeners) {
            if (id !== source) listener({ key, newValue: null });
        }
    }
}

class SharedChannels {
    constructor() {
        this.channels = [];
    }
    createClass() {
        const shared = this;
        return class BroadcastChannelMock {
            constructor(name) {
                this.name = name;
                this.listeners = [];
                shared.channels.push(this);
            }
            addEventListener(name, callback) {
                if (name === 'message') this.listeners.push(callback);
            }
            postMessage(data) {
                for (const channel of shared.channels) {
                    if (channel !== this && channel.name === this.name) {
                        for (const callback of channel.listeners) callback({ data });
                    }
                }
            }
        };
    }
}

async function flushPromises() {
    for (let index = 0; index < 8; index++) await Promise.resolve();
}

function createTab({ clock, storage, channels, enabled = true, fetchHandler = null, id }) {
    const nodes = new Map([
        ['session-countdown', new Element()],
        ['session-continue-btn', new Element()]
    ]);
    const documentListeners = {};
    const windowListeners = {};
    const document = {
        baseURI: 'http://localhost/UbeDelights/views/profile.php',
        body: {
            getAttribute(name) { return name === 'data-session-active' && enabled ? 'true' : null; },
            appendChild(node) { this.appended = node; }
        },
        createElement() { return new Element(); },
        getElementById(name) { return nodes.get(name) || null; },
        addEventListener(name, callback) { (documentListeners[name] ||= []).push(callback); },
        fire(name) { for (const callback of documentListeners[name] || []) callback({ target: document.body }); }
    };
    const location = {
        pathname: '/UbeDelights/views/profile.php',
        origin: 'http://localhost',
        href: 'http://localhost/UbeDelights/views/profile.php',
        replace(url) { this.href = url; }
    };
    const window = {
        location,
        addEventListener(name, callback) { (windowListeners[name] ||= []).push(callback); },
        BroadcastChannel: channels
    };
    storage.connect(id, event => {
        for (const callback of windowListeners.storage || []) callback(event);
    });
    const localStorage = {
        getItem(key) { return storage.values.get(key) ?? null; },
        setItem(key, value) { storage.set(id, key, value); },
        removeItem(key) { storage.remove(id, key); }
    };
    const requests = [];
    const context = vm.createContext({
        document,
        window,
        localStorage,
        BroadcastChannel: channels,
        URL,
        URLSearchParams,
        Number,
        Math,
        Object,
        String,
        Promise,
        Date: { now: () => clock.now },
        setTimeout: (callback, delay) => clock.schedule(callback, delay),
        clearTimeout: id => clock.clear(id),
        setInterval: (callback, delay) => clock.schedule(callback, delay, delay),
        clearInterval: id => clock.clear(id),
        fetch(url, options) {
            const action = new URLSearchParams(options.body).get('action');
            requests.push({ action, at: clock.now });
            const actionCount = requests.filter(request => request.action === action).length;
            const result = fetchHandler ? fetchHandler(action, actionCount) : { status: 200, data: { success: true } };
            if (result instanceof Error) return Promise.reject(result);
            return Promise.resolve({
                ok: result.status >= 200 && result.status < 300,
                status: result.status || 200,
                json: () => Promise.resolve(result.data || {})
            });
        }
    });
    const source = fs.readFileSync(path.join(__dirname, '../javascript/session_timeout.js'), 'utf8');
    vm.runInContext(source, context);
    return { document, nodes, location, requests };
}

async function run() {
    const publicClock = new FakeClock();
    const publicTab = createTab({
        clock: publicClock,
        storage: new SharedStorage(),
        channels: new SharedChannels().createClass(),
        enabled: false,
        id: 'public'
    });
    await publicClock.advanceTo(360000);
    assert.equal(publicTab.requests.length, 0);
    assert.equal(publicTab.document.body.appended, undefined);

    const clock = new FakeClock();
    const tab = createTab({
        clock,
        storage: new SharedStorage(),
        channels: new SharedChannels().createClass(),
        id: 'primary',
        fetchHandler(action, count) {
            if (action === 'continue' && count === 1) return { status: 403, data: { success: false } };
            if (action === 'expire' && count === 1) return { status: 409, data: { success: false, code: 'session_active', retry_after_seconds: 1 } };
            if (action === 'expire') return { status: 401, data: { success: false, code: 'session_timeout', login_url: '/UbeDelights/views/login.php?error=timeout' } };
            return { status: 200, data: { success: true } };
        }
    });
    await clock.advanceTo(240000);
    const overlay = tab.document.body.appended;
    assert.equal(overlay.style.display, 'flex');
    assert.equal(tab.nodes.get('session-countdown').textContent, '60');
    tab.nodes.get('session-continue-btn').fire('click');
    await flushPromises();
    assert.equal(overlay.style.display, 'flex');
    tab.nodes.get('session-continue-btn').fire('click');
    await flushPromises();
    assert.equal(overlay.style.display, 'none');
    await clock.advanceTo(480000);
    assert.equal(overlay.style.display, 'flex');
    await clock.advanceTo(540000);
    assert.equal(tab.requests.filter(request => request.action === 'expire').length, 1);
    await clock.advanceTo(541100);
    assert.equal(tab.location.href, '/UbeDelights/views/login.php?error=timeout');

    const sharedClock = new FakeClock();
    const sharedStorage = new SharedStorage();
    const sharedChannels = new SharedChannels().createClass();
    const activeTab = createTab({ clock: sharedClock, storage: sharedStorage, channels: sharedChannels, id: 'active' });
    const idleTab = createTab({ clock: sharedClock, storage: sharedStorage, channels: sharedChannels, id: 'idle' });
    await sharedClock.advanceTo(239000);
    activeTab.document.fire('mousemove');
    await flushPromises();
    await sharedClock.advanceTo(240000);
    assert.equal(idleTab.document.body.appended, undefined);
    await sharedClock.advanceTo(479000);
    assert.equal(idleTab.document.body.appended.style.display, 'flex');
    await sharedClock.advanceTo(480000);
    activeTab.document.fire('mousemove');
    await flushPromises();
    assert.equal(idleTab.document.body.appended.style.display, 'flex');
    assert.equal(idleTab.nodes.get('session-countdown').textContent, '300');
    activeTab.nodes.get('session-continue-btn').fire('click');
    await flushPromises();
    assert.equal(activeTab.document.body.appended.style.display, 'none');
    assert.equal(idleTab.document.body.appended.style.display, 'none');

    console.log('PASS: public pages stay inactive; warning, Continue, server retry, timeout redirect, and cross-tab activity work.');
}

run().catch(error => {
    console.error(error);
    process.exitCode = 1;
});
