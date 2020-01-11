import { existsSync, mkdirSync, readdirSync, readFileSync, writeFileSync } from 'fs';
import * as mjml from 'mjml';
import { basename, dirname, resolve } from 'path';

class MailCompiler {
    private readonly basePath = resolve(`${__dirname}/..`);
    private readonly mjmlPath = resolve(this.basePath + '/resources/views/mjml');

    process() {
        readdirSync(this.mjmlPath)
            .filter(d => ['common', 'compile.js'].indexOf(d) === -1)
            .filter(d => d !== 'common')
            .filter(s => s.endsWith('.mjml'))
            .map(file => this.compile(`${this.mjmlPath}/${file}`));
    }

    compile(src) {
        const content = readFileSync(src);
        const mjmlResult = mjml(content.toString(), {
            beautify: true,
            validationLevel: 'strict',
            filePath: src,
        });

        if (mjmlResult['errors'].length === 0) {
            console.log(`OK: ${basename(src)}`);
        } else {
            mjmlResult['errors'].map(error => {
                console.error(`${src}:${error.line}:1\n\t[${error.tagName}] ${error.message}`);
            });
        }

        if (mjmlResult['html'].length > 0) {
            // Fix dollar escape
            const content = mjmlResult['html'].replace(/&#36;/g, '$');
            const destFile = src.replace('/mjml/', '/mails/').replace('.mjml', '.blade.php');
            const destDir = dirname(destFile);

            if (!existsSync(destDir)) {
                mkdirSync(destDir, { recursive: true });
            }

            writeFileSync(destFile, content);
        }
    }
}

(new MailCompiler()).process();
