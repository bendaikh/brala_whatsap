/**
 * Bralam -> Google Sheets order webhook (v3: header-aware, plain-number prices).
 *
 * Rows are written by matching the HEADER NAMES in row 1 of the tab,
 * not by position. Any column order works, extra columns of your own
 * are left blank, and unknown headers are simply skipped.
 * If the tab is empty, the default Bralam header row is created.
 *
 * After pasting: Deploy -> Manage deployments -> Edit -> Version: New version -> Deploy
 * (the Web App URL stays the same).
 */
var BRALAM_HEADER_ROW = 1;

function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.waitLock(30000);
  try {
    var data = JSON.parse(e.postData.contents);
    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = (data.sheet_tab && ss.getSheetByName(data.sheet_tab)) || ss.getSheets()[0];
    var columns = data.columns || [];

    if (!columns.length) {
      throw new Error('Payload has no "columns" list. Update the Bralam app.');
    }

    var lastCol = sheet.getLastColumn();
    var headers = lastCol > 0
      ? sheet.getRange(BRALAM_HEADER_ROW, 1, 1, lastCol).getDisplayValues()[0]
      : [];
    var hasHeader = headers.some(function (h) { return String(h).trim() !== ''; });

    if (!hasHeader || sheet.getLastRow() === 0) {
      headers = columns
        .filter(function (c) { return c['default'] !== false; })
        .map(function (c) { return c.header; });
      sheet.getRange(BRALAM_HEADER_ROW, 1, 1, headers.length).setValues([headers]).setFontWeight('bold');
      sheet.setFrozenRows(BRALAM_HEADER_ROW);
    }

    // lookup: normalized key / header / alias -> column definition
    var lookup = {};
    columns.forEach(function (c) {
      [c.key, c.header].concat(c.aliases || []).forEach(function (name) {
        var k = norm(name);
        if (k && !lookup[k]) lookup[k] = c;
      });
    });

    var used = {};
    var matched = 0;
    var row = headers.map(function (h) {
      var c = lookup[norm(h)];
      if (!c || used[c.key]) return '';
      used[c.key] = true;
      matched++;
      return c.value === null || c.value === undefined ? '' : c.value;
    });

    if (matched === 0) {
      throw new Error('No header in row ' + BRALAM_HEADER_ROW + ' of "' + sheet.getName() +
        '" matches a Bralam field. Headers found: ' + headers.join(' | '));
    }

    var target = Math.max(sheet.getLastRow(), BRALAM_HEADER_ROW) + 1;
    var range = sheet.getRange(target, 1, 1, row.length);

    // Phone numbers / ids as text (keep leading 0 and +); price, total and qty
    // as plain numbers so the sheet never shows a currency symbol like "$".
    headers.forEach(function (h, i) {
      var c = lookup[norm(h)];
      if (!c) return;
      if (c.text) {
        sheet.getRange(target, i + 1).setNumberFormat('@');
      } else if (c.number) {
        var n = Number(c.value);
        if (c.value !== '' && c.value !== null && !isNaN(n)) {
          row[i] = n;
          sheet.getRange(target, i + 1).setNumberFormat(Math.floor(n) === n ? '0' : '0.00');
        }
      }
    });

    range.setValues([row]);

    return json({ success: true, row: target, matched: matched, columns: headers.length });
  } catch (err) {
    return json({ success: false, error: String(err) });
  } finally {
    lock.releaseLock();
  }
}

function norm(s) {
  return String(s === null || s === undefined ? '' : s)
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .toLowerCase().replace(/[^a-z0-9\u0600-\u06ff]+/g, '');
}

function json(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj))
    .setMimeType(ContentService.MimeType.JSON);
}
