1. Copy "bin/browser" folder to project root and rename to "doka"

2. Update "angular.json"

- Add "doka/doka.min.css" to "projects/angular/architect/build/options/styles"
- Add "doka/doka.min.js" to "projects/angular/architect/build/options/scripts"

3. Copy "angular-doka" to the "src" directory and import, see "src/app/app.module.ts"

4. Run `npm install src/angular-doka --save`, this solves issues with the production build