import { createViteConfig } from "vite-config-factory";

const entries = {
	'css/broken-link-detector': './source/sass/broken-link-detector.scss',
	'js/context-detector': './source/js/context-detector.ts',
	'js/editor-highlight': './source/js/editor-highlight.js'
}

export default createViteConfig(entries, {
	outDir: "assets/dist",
	manifestFile: "manifest.json",
});
