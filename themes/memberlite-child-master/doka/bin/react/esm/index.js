/* eslint-disable */

// Import Doka itself
import { supported } from './lib/doka.esm.min';
import './lib/doka.min.css';

// Import Doka React Components
import Doka from './components/Doka';
import DokaOverlay from './components/DokaOverlay';
import DokaModal from './components/DokaModal';

// Utils
import { toURL } from './utils/toURL';

// Import Styles for React Components
import './index.css';

// Test if Doka is supported on this environment
const isSupported = supported();

export {
    // Components
    Doka,
    DokaOverlay,
    DokaModal,

    // Utilities
    toURL,
    isSupported
}