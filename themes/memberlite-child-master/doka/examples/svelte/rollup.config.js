import svelte from 'rollup-plugin-svelte';
import resolve from 'rollup-plugin-node-resolve';
import commonjs from 'rollup-plugin-commonjs';
import livereload from 'rollup-plugin-livereload';
import { terser } from 'rollup-plugin-terser';

// DOKA: added to import Doka styles to components
import autoPreprocess from 'svelte-preprocess' // used to pre process <style> blocks
import inlineImports from 'postcss-import'; // used to inline @import statements
import postcss from 'postcss'; // remove duplicate @import statements in post processing
import discardDuplicates from 'postcss-discard-duplicates'; // remove duplicate @import statements in post processing
import fs from 'fs'; // used to save the css file


const production = !process.env.ROLLUP_WATCH;

export default {
	input: 'src/main.js',
	output: {
		sourcemap: true,
		format: 'iife',
		name: 'app',
		file: 'public/build/bundle.js'
	},
	plugins: [
		svelte({

			// DOKA: added `preprocess` block to inline Doka CSS @imports
			preprocess: autoPreprocess({
				postcss: {
					plugins: [
						inlineImports
					],
				}
			}),
	  
			// enable run-time checks when not in production
			dev: !production,

			// we'll extract any component CSS out into
			// a separate file — better for performance
			css: css => {

				// DOKA: added `css.write` statement so we can remove duplicate CSS caused by @imports
				const out = 'public/build/bundle.css';
				postcss([discardDuplicates])
					.process(css.code, { from: out })
					.then(result => {
						fs.writeFile(out, result.css, () => true)
						fs.writeFile(out + '.map', result.map, () => true)
					});

			}
		}),

		// If you have external dependencies installed from
		// npm, you'll most likely need these plugins. In
		// some cases you'll need additional configuration —
		// consult the documentation for details:
		// https://github.com/rollup/rollup-plugin-commonjs
		resolve({
			browser: true,
			dedupe: importee => importee === 'svelte' || importee.startsWith('svelte/')
		}),
		commonjs(),

		// In dev mode, call `npm run start` once
		// the bundle has been generated
		!production && serve(),

		// Watch the `public` directory and refresh the
		// browser on changes when not in production
		!production && livereload('public'),

		// If we're building for production (npm run build
		// instead of npm run dev), minify
		production && terser()
	],
	watch: {
		clearScreen: false
	}
};

function serve() {
	let started = false;

	return {
		writeBundle() {
			if (!started) {
				started = true;

				require('child_process').spawn('npm', ['run', 'start', '--', '--dev'], {
					stdio: ['ignore', 'inherit', 'inherit'],
					shell: true
				});
			}
		}
	};
}