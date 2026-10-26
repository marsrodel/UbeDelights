const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

class Element {
    constructor(id = '') {
        this.id = id;
        this.value = '';
        this.textContent = '';
        this.style = {};
        this.dataset = {};
        this.listeners = {};
        this.parentNode = null;
        this.classList = {
            add() {},
            remove() {},
            contains() { return false; }
        };
    }
    addEventListener(name, listener) {
        (this.listeners[name] ||= []).push(listener);
    }
    removeEventListener() {}
    querySelectorAll() { return []; }
    remove() {}
}

function createStorage(initial = {}) {
    const values = new Map(Object.entries(initial));
    return {
        values,
        getItem(key) { return values.has(key) ? values.get(key) : null; },
        setItem(key, value) { values.set(key, String(value)); },
        removeItem(key) { values.delete(key); }
    };
}

function loadLoginPage(error, savedLoginState = {}) {
    const username = new Element('username');
    const password = new Element('password');
    const eyeicon = new Element('eyeicon');
    const lockoutTimer = new Element('lockout-timer');
    const forgot = new Element('forgot-section');
    const orSeparator = new Element('or-separator');
    const form = new Element('form');
    form.querySelectorAll = () => [];
    const fieldGroup = new Element();
    const insertedErrors = [];
    fieldGroup.parentNode = { insertBefore(element) { insertedErrors.push(element); } };
    username.parentNode = fieldGroup;

    const nodes = new Map([
        ['username', username], ['password', password], ['eyeicon', eyeicon],
        ['lockout-timer', lockoutTimer], ['forgot-section', forgot],
        ['or-separator', orSeparator], ['form', form]
    ]);
    const documentListeners = {};
    const document = {
        title: 'Login',
        hidden: false,
        getElementById(id) { return nodes.get(id) || null; },
        querySelector(selector) { return selector === 'form' ? form : null; },
        querySelectorAll() { return []; },
        createElement(tag) { return new Element(tag); },
        addEventListener(name, listener) { (documentListeners[name] ||= []).push(listener); },
        removeEventListener() {}
    };
    const location = {
        href: 'http://localhost/UbeDelights/views/login.php?error=' + error,
        search: '?error=' + error,
        pathname: '/UbeDelights/views/login.php',
        referrer: '',
        reload() {},
        replace() {}
    };
    const windowListeners = {};
    const window = {
        location,
        history: { replaceState() {}, pushState() {} },
        addEventListener(name, listener) { (windowListeners[name] ||= []).push(listener); },
        removeEventListener() {}
    };
    const sessionStorage = createStorage({
        login_username: 'marsrodel_123',
        login_password: 'cached-secret'
    });
    const localStorage = createStorage(savedLoginState);
    const context = vm.createContext({
        document, window, sessionStorage, localStorage, URL, URLSearchParams, Date,
        history: window.history, setInterval: () => 1, clearInterval() {}, alert() {}
    });

    vm.runInContext(fs.readFileSync(path.join(__dirname, '../javascript/login.js'), 'utf8'), context);
    (documentListeners.DOMContentLoaded || []).forEach(listener => listener());

    return { username, password, forgot, orSeparator, lockoutTimer, insertedErrors, sessionStorage, localStorage };
}

const timeoutPage = loadLoginPage('timeout', { login_err_count: '1' });
assert.equal(timeoutPage.sessionStorage.getItem('login_password'), null);
assert.equal(timeoutPage.password.value, '');
assert.equal(timeoutPage.sessionStorage.getItem('login_username'), 'marsrodel_123');
assert.equal(timeoutPage.username.value, 'marsrodel_123');
assert.equal(timeoutPage.localStorage.getItem('login_err_count'), '1');
assert.equal(timeoutPage.forgot.style.display, 'none');
assert.equal(timeoutPage.orSeparator.style.display, 'none');

const timeoutWithOldFailures = loadLoginPage('timeout', { login_err_count: '2' });
assert.equal(timeoutWithOldFailures.localStorage.getItem('login_err_count'), '2');
assert.equal(timeoutWithOldFailures.forgot.style.display, 'none');

const timeoutDuringLockout = loadLoginPage('timeout', {
    login_err_count: '2',
    lock_deadline: String(Date.now() + 15000)
});
assert.equal(timeoutDuringLockout.forgot.style.display, 'none');

const failedLoginPage = loadLoginPage('pass', { login_err_count: '1' });
assert.equal(failedLoginPage.sessionStorage.getItem('login_password'), 'cached-secret');
assert.equal(failedLoginPage.password.value, 'cached-secret');
assert.equal(failedLoginPage.localStorage.getItem('login_err_count'), '2');
assert.equal(failedLoginPage.forgot.style.display, 'block');
assert.equal(failedLoginPage.lockoutTimer.textContent, 'Incorrect username or password.');

const blockedCustomerOrAdmin = loadLoginPage('blocked');
assert.equal(blockedCustomerOrAdmin.insertedErrors[0].textContent, 'Your account is blocked. Contact an admin or super admin.');
assert.equal(blockedCustomerOrAdmin.forgot.style.display, 'none');

const blockedSuperAdmin = loadLoginPage('blocked_super_admin');
assert.equal(blockedSuperAdmin.insertedErrors[0].textContent, 'Your account is blocked. Contact a super admin.');
assert.equal(blockedSuperAdmin.forgot.style.display, 'none');

console.log('PASS: timeout and blocked-login messages retain their expected behavior.');
