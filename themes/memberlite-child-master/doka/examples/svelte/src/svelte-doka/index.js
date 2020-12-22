/* eslint-disable */

import DokaModalComponent from './components/DokaModal.svelte';
import DokaComponent from './components/Doka.svelte';
import DokaOverlayComponent from './components/DokaOverlay.svelte';
import { create as createDoka } from './lib/doka.esm.min';

export const DokaOverlay = DokaOverlayComponent;
export const Doka = DokaComponent;
export const DokaModal = DokaModalComponent;
export const toURL = (src) => src instanceof Blob ? URL.createObjectURL(src) : src;
export const create = createDoka;