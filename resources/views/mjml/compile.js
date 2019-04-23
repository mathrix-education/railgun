class MailCompiler {
    constructor() {
        this.fs = require("fs");
        this.path = require("path");
        this.mjml = require("mjml");
    }

    process() {
        this.fs.readdirSync(__dirname)
            .filter(d => ["common", "compile.js"].indexOf(d) === -1)
            .filter(d => d !== "common")
            .map(dir => this.fs.readdirSync(`${__dirname}/${dir}`)
                .map(file => this.compile(`${__dirname}/${dir}/${file}`))
            )
    }

    compile(src) {
        const content = this.fs.readFileSync(src);
        const mjmlResult = this.mjml["mjml2html"](content.toString(), {
            beautify: true,
            validationLevel: "strict",
            filePath: src
        });
        console.log(this.path.dirname(src));

        if (mjmlResult.errors.length > 0) {
            mjmlResult.errors.map(error => {
                console.error(`${src}:${error.line}:1\n\t[${error.tagName}] ${error.message}`);
            });
        }

        if (mjmlResult["html"].length > 0) {
            const content = mjmlResult["html"].replace(/&#36;/g, "$");
            const destFile = src.replace("/mjml/", "/mails/").replace(".mjml", ".blade.php");
            const destDir = this.path.dirname(destFile);

            if (!this.fs.existsSync(destDir)) {
                this.fs.mkdirSync(destDir, {recursive: true});
            }

            this.fs.writeFileSync(destFile, content);
        }
    }
}

(new MailCompiler()).process();
