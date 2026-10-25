const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class Element {
    constructor(value = '') {
        this.value = value;
        this.hidden = false;
        this.innerHTML = '';
        this.listeners = {};
        this.classes = new Set();
        this.classList = {
            add: name => this.classes.add(name),
            remove: name => this.classes.delete(name),
        };
    }
    addEventListener(name, fn) { (this.listeners[name] ||= []).push(fn); }
    fire(name, event = {}) {
        for (const fn of this.listeners[name] || []) fn.call(this, { target: this, ...event });
    }
    querySelectorAll(selector) {
        if (selector !== '.pagination-link[data-page]') return [];
        if (this.linksHtml === this.innerHTML) return this.links;
        this.linksHtml = this.innerHTML;
        this.links = [...this.innerHTML.matchAll(/class="pagination-link" data-page="(\d+)"/g)].map(match => {
            const link = new Element();
            link.getAttribute = () => match[1];
            return link;
        });
        return this.links;
    }
}

const nodes = new Map();
for (const id of ['logsTypeFilter', 'logsSearch', 'logsFromDate', 'logsToDate',
    'btnApplyLogsFilter', 'btnClearLogsFilter', 'logsTableBody', 'logsTable',
    'emptyLogs', 'logsPaginationContainer', 'perPageSelect', 'successModalOkBtn',
    'successModal', 'successModalTitle', 'successModalMessage']) {
    nodes.set(id, new Element());
}
nodes.get('logsTypeFilter').value = 'authentication';
nodes.get('perPageSelect').value = '10';
const document = new Element();
document.body = new Element();
document.getElementById = id => nodes.get(id) || null;

const requests = [];
class Request {
    open(method, url) { this.url = new URL(url, 'http://localhost/UbeDelights/views/activity_logs.php'); }
    send() { requests.push(this); }
    abort() { this.aborted = true; }
    respond(status, data) {
        this.status = status;
        this.responseText = JSON.stringify(data);
        this.readyState = 4;
        this.onreadystatechange();
    }
}

const context = vm.createContext({
    document, window: {}, XMLHttpRequest: Request, URLSearchParams,
    setTimeout: () => 1, clearTimeout: () => {},
});
vm.runInContext(fs.readFileSync(path.join(__dirname, '../javascript/customer_logs.js'), 'utf8'), context);
document.fire('DOMContentLoaded');

const latest = () => requests.at(-1);
const param = key => latest().url.searchParams.get(key);
const reply = (logs = [], total = logs.length, page = 1, limit = 10) => latest().respond(200, {
    success: true, logs, pagination: { total, page, limit, totalPages: Math.max(1, Math.ceil(total / limit)) }
});

assert.equal(param('type'), 'authentication');
assert.equal(param('page'), '1');
reply();
assert.equal(nodes.get('logsTable').hidden, true);
assert.equal(nodes.get('emptyLogs').hidden, false);

nodes.get('logsSearch').value = 'profile';
nodes.get('logsFromDate').value = '2026-10-01';
nodes.get('logsToDate').value = '2026-10-25';
nodes.get('btnApplyLogsFilter').fire('click');
assert.equal(param('type'), 'authentication');
assert.equal(param('search'), 'profile');
reply([], 20);

const pageTwo = nodes.get('logsPaginationContainer').querySelectorAll('.pagination-link[data-page]')
    .find(link => link.getAttribute('data-page') === '2');
pageTwo.fire('click');
assert.equal(param('page'), '2');
reply([], 20, 2);

nodes.get('logsTypeFilter').value = 'activity';
nodes.get('logsTypeFilter').fire('change');
assert.equal(param('type'), 'activity');
assert.equal(param('page'), '1');
assert.equal(param('search'), 'profile');
assert.equal(param('date_from'), '2026-10-01');
assert.equal(param('date_to'), '2026-10-25');
reply();

nodes.get('btnClearLogsFilter').fire('click');
assert.equal(param('type'), 'activity');
assert.equal(param('search'), null);
assert.equal(param('date_from'), null);
assert.equal(param('date_to'), null);
assert.equal(nodes.get('logsTypeFilter').value, 'activity');
reply();

console.log('PASS: customer log type default, switching, filters, pagination, clear, and empty state.');
