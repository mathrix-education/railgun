#!/bin/ash
yarn install
yarn run docs:bundle
node deploy/steps/3_mjml_compile.js
