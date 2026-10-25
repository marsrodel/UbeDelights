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
        this.rectWidth = 0;
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
    querySelectorAll(selector) {
        if (selector === '.activity-badge') {
            return [...this.innerHTML.matchAll(/<span class="activity-badge [^"]*">([^<]*)<\/span>/g)].map(match => {
                const parentNode = {
                    appendChild(probe) { probe.parentNode = this; },
                    removeChild(probe) { probe.parentNode = null; }
                };
                const widths = { Login: 60, 'Failed Login': 110, 'Session Timeout': 140 };
                return {
                    parentNode,
                    cloneNode() {
                        return {
                            parentNode: null,
                            classList: { add() {} },
                            getBoundingClientRect() { return { width: widths[match[1]] || match[1].length * 8 + 20 }; }
                        };
                    }
                };
            });
        }
        this.links = [...this.innerHTML.matchAll(/data-page="(\d+)"/g)].map(match => {
            const link = new Element();
            link.getAttribute = () => match[1];
            return link;
        });
        return this.links;
    }
    getBoundingClientRect() {
        return { width: this.rectWidth };
    }
}

const nodes = new Map();
const pageSource = fs.readFileSync(path.join(__dirname, '../views/admin/system_logs.php'), 'utf8');
for (const match of pageSource.matchAll(/id="([^"]+)"/g)) nodes.set(match[1], new Element());
nodes.set('perPageSelect', new Element('10'));
nodes.get('logsTable').rectWidth = 1500;
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
    document, window: {}, URLSearchParams, XMLHttpRequest: Request, atob,
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
assert.equal(tableScroll.classes.has('logs-auth-action-compact'), true);

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
assert.equal(tableScroll.classes.has('logs-auth-action-compact'), true);

reply([{ ...authRow, action: 'failed_login' }]);
assert.equal(tableScroll.classes.has('logs-auth-action-medium'), true);
reply([{ ...authRow, action: 'session_timeout' }], 30);
assert.equal(tableScroll.classes.has('logs-auth-action-wide'), true);

nodes.get('logsPaginationContainer').links.find(link => link.getAttribute() === '2').fire('click');
assert.equal(current().url.searchParams.get('page'), '2');
reply([authRow], 30, 2);
nodes.get('logsTypeFilter').value = 'activity';
nodes.get('logsTypeFilter').fire('change');
assert.equal(current().url.searchParams.get('page'), '1');
assert.equal(current().url.searchParams.get('search'), 'account');
assert.equal(tableScroll.classes.has('logs-authentication-view'), false);
assert.equal(tableScroll.classes.has('logs-auth-action-compact'), false);
assert.equal(tableScroll.classes.has('logs-auth-action-medium'), false);
assert.equal(tableScroll.classes.has('logs-auth-action-wide'), false);
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

const deletionMetadata = metadata => ' [[DELETION_META_V1:' + Buffer.from(JSON.stringify(metadata), 'utf8').toString('base64') + ']]';
function openDeletionDetails() {
    const match = nodes.get('logsTableBody').innerHTML.match(/data-deletion-details-key="([^"]+)"/);
    assert.ok(match, 'Deletion log should have a View more link.');
    const button = { getAttribute: () => match[1] };
    nodes.get('logsTableBody').fire('click', {
        target: { closest: selector => selector === '[data-deletion-details-key]' ? button : null }
    });
    assert.equal(nodes.get('deletionDetailsModal').classes.has('active'), true);
    return nodes.get('deletionDetailsBody').innerHTML;
}
function closeDeletionDetails() {
    nodes.get('deletionDetailsDoneBtn').fire('click');
    assert.equal(nodes.get('deletionDetailsModal').classes.has('active'), false);
}

const directDeleteLog = {
    ...authRow, id: 501, log_type: 'activity', idNumber: '2026-0012', username: 'edrian_123',
    fullName: 'Edrian Prones', role: 'super_admin', module: 'User Management', action: 'DELETE_USER',
    created_at: '2026-10-20 14:30:00', details: 'edrian_123 deleted marsrodel_123 (ID: 2026-0013)' +
        deletionMetadata({ kind: 'direct_delete', reason: 'Policy request: <script>bad()</script>' }) +
        ' | Browser: Chrome 150.0.0.0 | OS: Windows'
};
reply([directDeleteLog]);
assert.match(nodes.get('logsTableBody').innerHTML, /edrian_123 deleted marsrodel_123/);
assert.match(nodes.get('logsTableBody').innerHTML, />View more</);
assert.doesNotMatch(nodes.get('logsTableBody').innerHTML, /DELETION_META_V1|Browser:/);
let detailHtml = openDeletionDetails();
assert.equal(nodes.get('deletionDetailsTitle').textContent, 'Account Deletion Details');
assert.match(detailHtml, /Deleted by/);
assert.match(detailHtml, /edrian_123/);
assert.match(detailHtml, /Deleted on/);
assert.match(detailHtml, /Reason/);
assert.match(detailHtml, /&lt;script&gt;bad\(\)&lt;\/script&gt;/);
closeDeletionDetails();

const requestLog = {
    ...directDeleteLog, id: 502, action: 'DELETION_REQUEST', username: 'admin_123', idNumber: '2026-0010',
    fullName: 'Shania Bugahod', created_at: '2026-10-20 14:00:00',
    details: 'admin_123 requested deletion of marsrodel_123 (ID: 2026-0013)' + deletionMetadata({
        kind: 'deletion_request', request_id: 44, requested_by: 'admin_123', requested_by_id: '2026-0010',
        requested_on: '2026-10-20 13:59:00', reason: 'Duplicate account'
    })
};
reply([requestLog]);
detailHtml = openDeletionDetails();
assert.equal(nodes.get('deletionDetailsTitle').textContent, 'Deletion Request Details');
assert.match(detailHtml, /Requested by/);
assert.match(detailHtml, /Requested on/);
assert.match(detailHtml, /Duplicate account/);
closeDeletionDetails();

const approvedReviewLog = {
    ...directDeleteLog, id: 503, action: 'DELETION_REVIEW', username: 'marsrodel_123',
    idNumber: '2026-0013', created_at: '2026-10-20 14:30:00',
    details: 'marsrodel_123 approved deletion request for marsrodel_123 (ID: 2026-0013)' + deletionMetadata({
        kind: 'deletion_review', outcome: 'approved', requested_by: 'admin_123', requested_by_id: '2026-0010',
        requested_on: '2026-10-20 13:59:00', reason: 'Duplicate account'
    })
};
reply([approvedReviewLog]);
detailHtml = openDeletionDetails();
assert.equal(nodes.get('deletionDetailsTitle').textContent, 'Deletion Approval Details');
assert.match(detailHtml, /Approved by/);
assert.match(detailHtml, /Approved on/);
assert.doesNotMatch(detailHtml, /Reviewed by/);
closeDeletionDetails();

const rejectedReviewLog = {
    ...approvedReviewLog, id: 504,
    details: 'marsrodel_123 rejected deletion request for marsrodel_123 (ID: 2026-0013)' + deletionMetadata({
        kind: 'deletion_review', outcome: 'rejected', requested_by: 'admin_123', requested_by_id: '2026-0010',
        requested_on: '2026-10-20 13:59:00', reason: 'Duplicate account'
    })
};
reply([rejectedReviewLog]);
detailHtml = openDeletionDetails();
assert.equal(nodes.get('deletionDetailsTitle').textContent, 'Deletion Request Review');
assert.match(detailHtml, /Reviewed by/);
assert.match(detailHtml, /Reviewed on/);
assert.doesNotMatch(detailHtml, /Approved by/);
closeDeletionDetails();

reply([{ ...rejectedReviewLog, id: 505, details: 'marsrodel_123 rejected deletion request for an older entry' }]);
detailHtml = openDeletionDetails();
assert.match(detailHtml, /Not recorded for this historical entry\./);
closeDeletionDetails();

reply([{ ...rejectedReviewLog, id: 506, details: 'marsrodel_123 rejectedd deletion request for an older entry' }]);
detailHtml = openDeletionDetails();
assert.equal(nodes.get('deletionDetailsTitle').textContent, 'Deletion Request Review Details');
assert.match(detailHtml, /Review outcome/);
assert.doesNotMatch(detailHtml, /Approved by/);
closeDeletionDetails();

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
console.log('PASS: log selector, defaults, filters, pagination, timestamps, escaping, deletion details, request races, and Notice modal.');
