const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../javascript/forgot_password.js'), 'utf8');

function inlineErrorFor(code) {
  const nodes = new Map();
  const container = { insertBefore(element) { nodes.set(element.id, element); } };
  const wrapper = { parentNode: container };
  const idField = { closest() { return wrapper; } };
  nodes.set('id_number', idField);
  let onReady;
  const document = {
    getElementById(id) { return nodes.get(id) || null; },
    querySelector() { return null; },
    querySelectorAll() { return []; },
    createElement() { return { id: '', className: '', textContent: '' }; },
    addEventListener(name, callback) { if (name === 'DOMContentLoaded') onReady = callback; }
  };
  const window = { location: { search: `?step=1&error=${code}` } };
  vm.runInNewContext(source, { document, window, URLSearchParams });
  onReady();
  return nodes.get('id_number-error');
}

const errors = {
  pending_id: 'Your account is still pending approval. Please wait for approval.',
  incomplete_id: 'This account setup is not complete yet. Please complete it before resetting your password.',
  expired_id: 'Your account invitation has expired. Please contact an administrator.',
  inactive_id: 'This account is not active. Please contact an administrator.',
  blocked_id: 'Your account is blocked. Please contact an administrator.'
};

for (const [code, message] of Object.entries(errors)) {
  const error = inlineErrorFor(code);
  assert.ok(error, `No inline error for ${code}`);
  assert.equal(error.className, 'inline-error');
  assert.equal(error.textContent, message);
}

console.log('PASS: recovery status messages appear below the ID field.');
