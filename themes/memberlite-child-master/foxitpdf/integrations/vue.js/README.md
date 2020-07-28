# FoxitPDFSDK for Web Example - Vue.js

This guide shows two examples. One introduces how to quickly run the boilerplate sample for Vue.js inside FoxitPDFSDK for Web, and the other presents a way to integrate FoxitPDFSDK for Web into an exiting Vue app.

## Quickly run the built-in example for Vue.js

FoxitPDFSDK for Web provides a boilerplate project for [@vue/cli](https://cli.vuejs.org/guide/) app. This example can be found at `../integrations/` inside FoxitPDFSDK for Web package.

### Prerequisites

- [Nodejs](https://nodejs.org/en/) and [npm](https://www.npmjs.com)
- [FoxitPDFSDK for Web](https://developers.foxitsoftware.com/pdf-sdk/Web)

### Getting started

Enter `../integratons/vue.js/` inside FoxitPDFSDK for Web, and run:

```sh
npm run setup
```

This setup will implement the followings:

- `npm install` - install dependencies.
- `npm run update-sdk`
  - Copy `lib` folder from the root folder to the `../integratons/vue.js/src/`, and auto rename it to `foxit-lib`.
  - Copy `..examples/license-key.js`to the `../integratons/react.js/src/`.

### Runnning the example

```sh
npm run serve
```

Now you are ready to launch the app. Open your browser, navigate to `<http://127.0.0.1:9103/>` to load your example.

## Integrate FoxitPDFSDK for Web into existing Vue.js project

This integration assumes you have [@vue/cli](https://cli.vuejs.org/guide/) app installed with _all default settings_.

### Prerequisites

- [Nodejs](https://nodejs.org/en/) and [npm](https://www.npmjs.com)
- [@vue/cli](https://cli.vuejs.org/guide/)
- [FoxitPDFSDK for Web](https://developers.foxitsoftware.com/pdf-sdk/Web)

### Setup

Let's call the root folder of your exiting project as VueJS and FoxitPDFSDK for Web as SDK.

1. In SDK, duplicate the following folder and files to `VueJS/src`, and change the `lib` name to `foxit-lib`.

   - The `lib`folder. 
   - The `../examples/license-key.js` file.
   - The `../integrations/vue.js/src/preload.js` file

   Besides, to correctly referene your fonts lib, you also need to duplicate the `external` folder inside SDK to `VueJS/src/foxit-lib/assets`. 

2. Run `npm i -D cross-env` to install `cross-env`, and add the following segments to `serve` and `build` in `VueJS/package.json`.

   ```sh
   cross-env NODE_OPTIONS=--max_old_space_size=8192 NODE_ENV=development vue-cli-service serve
   cross-env NODE_OPTIONS=--max_old_space_size=8192 NODE_ENV=production vue-cli-service build
   ```

   _The purpose of this step is to void memory leak error._

### Configuration

Add configurations onto the following files in your VueJS:

- `vue.config.js`
- `.eslintignore`

For the configuration details, refer to the counterpart files in SDK.

### Component

- Create `src/components/PDFViewer.vue` in your VueJS，and reference it in `src/App.vue`.

For the configuration details, refer to the counterpart files in SDK.

### Running your application

```sh
npm run serve
```

Now everything is set up. Open your browser, input the address based on you set up to launch your application.
