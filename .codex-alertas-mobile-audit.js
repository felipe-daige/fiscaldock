import fs from 'node:fs';
import { createRequire } from 'node:module';

const require = createRequire(import.meta.url);
const puppeteer = require('/tmp/node_modules/puppeteer');
const email = process.env.FISCALDOCK_AUDIT_EMAIL;
const password = process.env.FISCALDOCK_AUDIT_PASSWORD;
const baseUrl = (process.env.FISCALDOCK_AUDIT_BASE_URL || 'https://fiscaldock.com.br').replace(/\/$/, '');
const entryPath = process.env.FISCALDOCK_AUDIT_ENTRY_PATH || '';
const dataPrefix = process.env.FISCALDOCK_AUDIT_DATA_PREFIX || '';
const output = process.argv[2] || '/tmp/alertas-mobile.png';

if (!entryPath && (!email || !password)) {
    throw new Error('Credenciais de auditoria ausentes.');
}

const browser = await puppeteer.launch({
    executablePath: '/usr/bin/chromium',
    headless: true,
    args: ['--no-sandbox', '--disable-gpu', '--hide-scrollbars'],
});

try {
    const page = await browser.newPage();
    const browserErrors = [];
    page.on('pageerror', (error) => browserErrors.push(error.message));
    await page.setViewport({ width: 390, height: 844, deviceScaleFactor: 1, isMobile: true, hasTouch: true });
    if (dataPrefix) {
        await page.setRequestInterception(true);
        page.on('request', async (request) => {
            const url = new URL(request.url());
            const match = url.pathname.match(/^\/app\/alertas\/(dados|resumo|evolucao)$/);
            if (!match) {
                request.continue();
                return;
            }

            const proxyUrl = baseUrl + dataPrefix + '/' + match[1] + url.search;
            const response = await fetch(proxyUrl);
            request.respond({
                status: response.status,
                contentType: response.headers.get('content-type') || 'application/json',
                body: await response.text(),
            });
        });
    }
    if (entryPath) {
        const response = await page.goto(baseUrl + entryPath, { waitUntil: 'domcontentloaded', timeout: 10000 });
        if (!response || !response.ok()) {
            const body = response ? await response.text() : '';
            throw new Error('Preview respondeu com HTTP ' + (response ? response.status() : 'desconhecido') + ': ' + body.slice(0, 500));
        }
    } else {
        await page.goto(baseUrl + '/login', { waitUntil: 'networkidle2' });

        const aceitar = await page.$('#cookie-consent-accept');
        if (aceitar) await aceitar.click();

        await page.type('#email', email);
        await page.type('#password', password);
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'networkidle2', timeout: 10000 }).catch(() => null),
            page.evaluate(() => HTMLFormElement.prototype.submit.call(document.getElementById('login-form'))),
        ]);
    }
    if (!entryPath) {
        await page.goto(baseUrl + '/app/alertas', { waitUntil: 'networkidle2' });
    }
    await page.waitForSelector('#alertas-lista', { timeout: 10000 });
    await new Promise((resolve) => setTimeout(resolve, 2500));
    if (process.env.FISCALDOCK_AUDIT_EXPAND === '1') {
        await page.evaluate(() => {
            document.querySelectorAll('.alerta-grupo-header').forEach((header) => header.click());
        });
        await new Promise((resolve) => setTimeout(resolve, 500));
    }

    const audit = await page.evaluate(() => {
        const root = document.documentElement;
        const byId = (id) => {
            const element = document.getElementById(id);
            if (!element) return null;
            const rect = element.getBoundingClientRect();
            return { id, left: rect.left, right: rect.right, width: rect.width, height: rect.height };
        };
        const overflowing = Array.from(document.querySelectorAll('body *')).filter((element) => {
            const rect = element.getBoundingClientRect();
            return rect.right > root.clientWidth + 1 || rect.left < -1;
        }).slice(0, 25).map((element) => ({
            tag: element.tagName,
            id: element.id,
            className: String(element.className || '').slice(0, 180),
            left: Math.round(element.getBoundingClientRect().left),
            right: Math.round(element.getBoundingClientRect().right),
        }));
        return {
            url: location.href,
            title: document.title,
            viewport: root.clientWidth,
            scrollWidth: root.scrollWidth,
            pageHeight: Math.max(document.body.scrollHeight, root.scrollHeight),
            alerts: document.querySelectorAll('.alerta-grupo-header').length,
            filterEnhanced: Boolean(document.querySelector('.mobile-filters-enhanced')),
            filterState: (() => {
                const filter = document.querySelector('[data-mobile-filters]');
                return filter ? {
                    className: filter.className,
                    ready: filter.dataset.mobileFiltersReady || null,
                    childClasses: Array.from(filter.children).map((child) => child.className),
                } : null;
            })(),
            regions: ['alertas-central-container', 'alertas-tabs-nav', 'alertas-lista', 'alertas-paginacao'].map(byId),
            overflowing,
        };
    });
    audit.browserErrors = browserErrors;

    await page.screenshot({ path: output, fullPage: true });
    fs.writeFileSync(output.replace(/\.png$/, '.json'), JSON.stringify(audit, null, 2));
    process.stdout.write(JSON.stringify(audit, null, 2) + '\n');
} finally {
    await browser.close();
}
