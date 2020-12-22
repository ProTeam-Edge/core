# AngularDoka

This library was generated with [Angular CLI](https://github.com/angular/angular-cli) version 8.2.3.

## Usage instructions

1. Copy "bin/browser" folder to project root and rename to "doka"

2. Update "angular.json"

- Add "doka/doka.min.css" to "projects/angular/architect/build/options/styles"
- Add "doka/doka.min.js" to "projects/angular/architect/build/options/scripts"

3. Copy "angular-doka" to the "src" directory and import, see "src/app/app.module.ts"



## Code scaffolding

Run `ng generate component component-name --project angular-doka` to generate a new component. You can also use `ng generate directive|pipe|service|class|guard|interface|enum|module --project angular-doka`.
> Note: Don't forget to add `--project angular-doka` or else it will be added to the default project in your `angular.json` file. 


## Build

Run `ng build angular-doka` to build the project. The build artifacts will be stored in the `dist/` directory.


## Publishing

After building your library with `ng build angular-doka`, go to the dist folder `cd dist/angular-doka` and run `npm publish`.

## Running unit tests

Run `ng test angular-doka` to execute the unit tests via [Karma](https://karma-runner.github.io).

## Further help

To get more help on the Angular CLI use `ng help` or go check out the [Angular CLI README](https://github.com/angular/angular-cli/blob/master/README.md).
