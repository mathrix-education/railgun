const fs = require("fs");
const csvtojson = require("csvtojson");
const writeYamlFile = require('write-yaml-file');

const scopesCsvFile = "./src/scopes.csv";
const tagsYaml = "./src/includes/tags.yaml";
const securitySchemesYaml = "./src/includes/security-schemes.yaml";

!fs.existsSync("./src/includes") ? fs.mkdirSync("./src/includes") : null;

/**
 * Generate a file index
 * @param what
 * @returns {Promise<void>}
 */
const generateIndex = async (what) => {
    const files = fs.readdirSync(`./src/${what}`);
    const filesIndex = {};
    files.map(file => {
        const name = file.split(".")[0];
        filesIndex[name] = {
            "$ref": `../${what}/${file}`
        };
    });
    await writeYamlFile(`./src/includes/${what}.yaml`, filesIndex);
};

/**
 * Generate scopes entries based on the scopes CSV file.
 * @returns {Promise<void>}
 */
const generateScopes = async () => {
    const scopes = await csvtojson().fromFile(scopesCsvFile);
    const securitySchemes = {};
    scopes.map((scope) => {
        securitySchemes[scope.name] = {
            description: scope.description,
            type: "http",
            scheme: "bearer",
            bearerFormat: "JWT"
        };
    });
    await writeYamlFile(securitySchemesYaml, securitySchemes);
};

/**
 * Generate tags entries.
 * @returns {Promise<void>}
 */
const generateTags = async () => {
    const tags = [
        "Auth",
        "CDN",
        "Checks",
        "Debug",
        "Profile",
        "Questions",
        "Series",
        "Situations",
        "Tags",
        "Themes",
        "Users"
    ];
    const tagsParsed = tags.map(tag => {
        return {
            name: tag,
            description: `${tag} API`
        }
    });
    await writeYamlFile(tagsYaml, tagsParsed);
};

generateIndex("requestBodies");
generateIndex("responses");
generateIndex("schemas");
// generateScopes();
generateTags();
