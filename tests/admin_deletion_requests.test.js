const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class Element {
    constructor() {
        this.style = {};
        this.value = '';
        this.textContent = '';
        this.innerHTML = '';
        this.disabled = false;
        this.classes = new Set();
        this.classList = {
            add: name => this.classes.add(name),
            remove: name => this.classes.delete(name),
        };
    }
}

const nodes = new Map();
for (const id of ['drPasswordModal', 'drPasswordTitle', 'drPasswordInput', 'drPasswordError',
    'drPasswordConfirmBtn', 'drSuccessModal', 'drSuccessMessage']) {
    nodes.set(id, new Element());
}
nodes.get('drPasswordModal').classList.add('active');

const document = {
    body: new Element(),
    addEventListener() {},
    querySelectorAll() { return []; },
    getElementById(id) { return nodes.get(id) || null; },
};

const requests = [];
class Request {
    constructor() { requests.push(this); }
    open(method, url) { this.method = method; this.url = url; }
    send(body) { this.body = body; }
    respond(status, data) {
        this.status = status;
        this.responseText = JSON.stringify(data);
        this.onload();
    }
}
class FormData {
    constructor() { this.fields = new Map(); }
    append(key, value) { this.fields.set(key, value); }
}

const context = vm.createContext({ document, XMLHttpRequest: Request, FormData, deletionRequests: [] });
vm.runInContext(fs.readFileSync(path.join(__dirname, '../javascript/admin_deletion_requests.js'), 'utf8'), context);

context.reviewDeletionRequest('10', 'approve');
assert.equal(requests.length, 1);
assert.equal(requests[0].body.fields.get('action'), 'approve');
assert.equal(nodes.get('drPasswordConfirmBtn').disabled, true);
assert.equal(nodes.get('drPasswordModal').classes.has('active'), true);
context.closeModal('drPasswordModal');
assert.equal(nodes.get('drPasswordModal').classes.has('active'), true);
context.reviewDeletionRequest('10', 'approve');
assert.equal(requests.length, 1, 'a second click must not send another approval');

requests[0].respond(500, { success: false, message: 'Approval was rolled back.' });
assert.equal(nodes.get('drPasswordError').textContent, 'Approval was rolled back.');
assert.equal(nodes.get('drPasswordConfirmBtn').textContent, 'Retry Approval');
assert.equal(nodes.get('drPasswordModal').classes.has('active'), true);

context.reviewDeletionRequest('10', 'approve');
requests[1].respond(200, { success: true, message: 'Account deleted.' });
assert.equal(nodes.get('drPasswordModal').classes.has('active'), false);
assert.equal(nodes.get('drSuccessModal').classes.has('active'), true);
assert.equal(nodes.get('drSuccessMessage').textContent, 'Account deleted.');

nodes.get('drPasswordModal').classList.add('active');
context.reviewDeletionRequest('11', 'reject');
requests[2].respond(409, { success: false, code: 'request_already_reviewed', message: 'Already reviewed.' });
assert.equal(nodes.get('drPasswordConfirmBtn').textContent, 'Refresh Page');
assert.equal(nodes.get('drPasswordError').textContent, 'Already reviewed.');
assert.equal(nodes.get('drPasswordModal').classes.has('active'), true);

console.log('PASS: deletion approval processing, rollback feedback, success, and review conflict.');
