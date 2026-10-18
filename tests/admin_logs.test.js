const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

class Element {
    constructor(value = '') {
        this.value = value;
        this.hidden = false;
        this.innerHTML = '';
        this.textContent = '';
        this.listeners = {};
        this.classes = new Set();
        this.classList = {
            add: name => this.classes.add(name),
            remove: name => this.classes.delete(name),
            toggle: (name, force) => {
                const shouldAdd = force === undefined ? !this.classes.has(name) : force;
                if (shouldAdd) this.classes.add(name);
                else this.classes.delete(name);
                return shouldAdd;
            }
        };
    }
    addEventListener(name, fn) {
        (this.listeners[name] ||= []).push(fn);
    }
    fire(name, detail = {}) {
        for (const fn of this.listeners[name] || []) fn.call(this, { target: this, ...detail });
    }
    querySelectorAll() {
        this.links = [...this.innerHTML.matchAll(/data-page="(\d+)"/g)].map(match => {
            const link = new Element();
            link.getAttribute = () => match[1];
            return link;
        });
        return this.links;
    }
}

const nodes = new Map();
const pageSource = fs.readFileSync(path.join(__dirname, '../views/admin/system_logs.php'), 'utf8');
for (const match of pageSource.matchAll(/id="([^"]+)"/g)) nodes.set(match[1], new Element());
nodes.set('perPageSelect', new Element('10'));
const document = new Element();
document.body = new Element();
document.getElementById = id => nodes.get(id) || null;
const tableScroll = new Element();
document.querySelector = selector => selector === '.logs-table-scroll' ? tableScroll : null;
const requests = [];
class Request {
    open(method, url) {
        this.url = new URL(url, 'http://localhost/UbeDelights/views/admin/system_logs.php');
    }
    send() { requests.push(this); }
    abort() { this.aborted = true; this.respond(0, {}); }
    respond(status, data) {
        this.status = status;
        this.readyState = 4;
        this.responseText = JSON.stringify(data);
        this.onreadystatechange();
    }
}
const context = vm.createContext({
    document, window: {}, URLSearchParams, XMLHttpRequest: Request,
    setTimeout: () => 1, clearTimeout: () => {}
});
nodes.get('logsTypeFilter').value = 'activity';
vm.runInContext(fs.readFileSync(path.join(__dirname, '../javascript/admin_logs.js'), 'utf8'), context);
document.fire('DOMContentLoaded');
const current = () => requests.at(-1);
const reply = (logs = [], total = logs.length, page = 1, limit = 10) => current().respond(200, {
    success: true, logs, pagination: { total, page, limit, totalPages: Math.max(1, Math.ceil(total / limit)) }
});

assert.equal(nodes.get('logsTypeFilter').value, 'authentication');
assert.equal(current().url.searchParams.get('type'), 'authentication');
reply();
assert.equal(nodes.get('logsTable').hidden, true);
assert.equal(nodes.get('emptyLogs').hidden, false);
assert.equal(nodes.get('emptyLogsTitle').textContent, 'No login & security logs');
assert.match(nodes.get('logsTableHead').innerHTML, /Time In/);
assert.match(nodes.get('logsTableHead').innerHTML, /Time Out/);
assert.doesNotMatch(nodes.get('logsTableHead').innerHTML, /Severity/);
assert.equal(tableScroll.classes.has('logs-authentication-view'), true);

nodes.get('logsSearch').value = 'account';
nodes.get('logsRoleFilter').value = 'admin';
nodes.get('logsFromDate').value = '2026-10-01';
nodes.get('logsToDate').value = '2026-10-18';
nodes.get('logsTypeFilter').value = 'authentication';
nodes.get('logsTypeFilter').fire('change');
assert.equal(current().url.searchParams.get('type'), 'authentication');
assert.equal(current().url.searchParams.get('search'), 'account');
assert.equal(current().url.searchParams.get('role'), 'admin');
assert.equal(current().url.searchParams.get('date_from'), '2026-10-01');
assert.equal(current().url.searchParams.get('date_to'), '2026-10-18');
assert.equal(tableScroll.classes.has('logs-authentication-view'), true);
const authRow = {
    id: 1, idNumber: '2026-0001', fullName: '<img src=x onerror=bad()>', role: 'admin',
    module: 'Authentication', action: 'login', severity: 'INFO', browser: 'Chrome 150.0.0.0',
    os: 'Windows', created_at: '2026-10-18 10:00:00', time_in: '2026-10-18 10:00:00',
    time_out: '2026-10-18 10:30:00', ip_address: '::1', details: '<script>bad()</script>'
};
reply([authRow], 30);
assert.match(nodes.get('logsTableHead').innerHTML, /Time In/);
assert.match(nodes.get('logsTableHead').innerHTML, /Time Out/);
assert.doesNotMatch(nodes.get('logsTableHead').innerHTML, /Severity/);
assert.equal((nodes.get('logsTableHead').innerHTML.match(/<th /g) || []).length, 10);
assert.equal((nodes.get('logsTableBody').innerHTML.match(/<td(?:\s|>)/g) || []).length, 10);
assert.doesNotMatch(nodes.get('logsTableBody').innerHTML, /data-label="Severity"|log-severity/);
assert.match(nodes.get('logsTableBody').innerHTML, /data-label="Time In"/);
assert.match(nodes.get('logsTableBody').innerHTML, /data-label="Time Out"/);
assert.match(nodes.get('logsTableBody').innerHTML, /&lt;script&gt;/);
assert.match(nodes.get('logsTableBody').innerHTML, /&lt;img/);
assert.doesNotMatch(nodes.get('logsTableBody').innerHTML, /style=/);
assert.match(nodes.get('logsTableBody').innerHTML, /10:30 AM/);
assert.match(nodes.get('logsTableBody').innerHTML, /127\.0\.0\.1/);

nodes.get('logsPaginationContainer').links.find(link => link.getAttribute() === '2').fire('click');
assert.equal(current().url.searchParams.get('page'), '2');
reply([authRow], 30, 2);
nodes.get('logsTypeFilter').value = 'activity';
nodes.get('logsTypeFilter').fire('change');
assert.equal(current().url.searchParams.get('page'), '1');
assert.equal(current().url.searchParams.get('search'), 'account');
assert.equal(tableScroll.classes.has('logs-authentication-view'), false);
assert.doesNotMatch(nodes.get('logsTableHead').innerHTML, /Severity/);
const stale = current();
nodes.get('logsTypeFilter').value = 'system';
nodes.get('logsTypeFilter').fire('change');
assert.equal(stale.aborted, true);
reply();
stale.respond(200, { success: true, logs: [authRow], pagination: { total: 1, page: 1, limit: 10, totalPages: 1 } });
assert.equal(nodes.get('logsTableBody').innerHTML, '');
assert.equal(nodes.get('emptyLogsTitle').textContent, 'No system logs');

nodes.get('logsTypeFilter').value = 'system';
nodes.get('logsTypeFilter').fire('change');
reply([authRow]);
assert.doesNotMatch(nodes.get('logsTableHead').innerHTML, /Severity/);
assert.equal((nodes.get('logsTableBody').innerHTML.match(/<td(?:\s|>)/g) || []).length, 9);
assert.doesNotMatch(nodes.get('logsTableBody').innerHTML, /log-severity/);

nodes.get('logsTypeFilter').value = 'activity';
nodes.get('logsTypeFilter').fire('change');
reply([{ ...authRow, action: 'PROFILE_UPDATE', time_in: null, time_out: null }]);
assert.match(nodes.get('logsTableHead').innerHTML, /Timestamp/);
assert.doesNotMatch(nodes.get('logsTableHead').innerHTML, /Time Out/);
assert.doesNotMatch(nodes.get('logsTableHead').innerHTML, /Severity/);
assert.equal((nodes.get('logsTableBody').innerHTML.match(/<td(?:\s|>)/g) || []).length, 9);
assert.doesNotMatch(nodes.get('logsTableBody').innerHTML, /log-severity/);
assert.match(nodes.get('logsTableBody').innerHTML, /10:00 AM/);

nodes.get('btnClearLogsFilter').fire('click');
assert.equal(current().url.searchParams.get('type'), 'activity');
for (const key of ['search', 'role', 'date_from', 'date_to']) assert.equal(current().url.searchParams.has(key), false);
reply();
nodes.get('perPageSelect').value = '25';
nodes.get('perPageSelect').fire('change');
assert.equal(current().url.searchParams.get('limit'), '25');
assert.equal(current().url.searchParams.get('page'), '1');
current().respond(400, { success: false, message: 'Enter a valid date.' });
assert.equal(nodes.get('successModalTitle').textContent, 'Notice');
assert.equal(nodes.get('successModalMessage').textContent, 'Enter a valid date.');
assert.equal(nodes.get('successModal').classes.has('active'), true);
nodes.get('successModalOkBtn').fire('click');
assert.equal(nodes.get('successModal').classes.has('active'), false);
assert.equal(document.body.classes.has('logs-modal-open'), false);
console.log('PASS: log selector, defaults, filters, pagination, timestamps, escaping, request races, and Notice modal.');
