#!/usr/bin/env node
class MailCompiler {
    constructor()
    {
        this.fs   = require("fs");
        this.path = require("path");
        this.mjml = require("mjml");

        this.basePath = this.path.resolve(`${__dirname}/..`);
        this.mjmlPath = this.path.resolve(this.basePath + '/resources/views/mjml');
    }

    process()
    {
        this.fs.readdirSync(this.mjmlPath)
            .filter(d => ["common", "compile.js"].indexOf(d) === -1)
            .filter(d => d !== "common")
            .filter(s => s.endsWith('.mjml'))
            .map(file => this.compile(`${this.mjmlPath}/${file}`));
    }

    compile(src)
    {
        const content    = this.fs.readFileSync(src);
        const mjmlResult = this.mjml(content.toString(), {
            beautify: true,
            validationLevel: "strict",
            filePath: src
        });

        if (mjmlResult['errors'].length === 0) {
            console.log(`OK: ${this.path.basename(src)}`);
        } else {
            mjmlResult['errors'].map(error => {
                console.error(`${src}:${error.line}:1\n\t[${error.tagName}] ${error.message}`);
            });
        }

        if (mjmlResult["html"].length > 0) {
            // Fix dollar escape
            const content  = mjmlResult["html"].replace(/&#36;/g, "$");
            const destFile = src.replace("/mjml/", "/mails/").replace(".mjml", ".blade.php");
            const destDir  = this.path.dirname(destFile);

            if (!this.fs.existsSync(destDir)) {
                this.fs.mkdirSync(destDir, {recursive: true});
            }

            this.fs.writeFileSync(destFile, content);
        }
    }
}

(new MailCompiler()).process();
