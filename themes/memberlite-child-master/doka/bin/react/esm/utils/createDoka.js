/* eslint-disable */

import { create, supported, OptionTypes } from '../lib/doka.esm.min';

// Test if Doka is supported on this environment
const isSupported = supported();

// These keys are removed before passing options to Doka
const filteredKeys = [
    'id',
    'className'
];

// filtered methods
const filteredMethods = [
    'setOptions',
    'on',
    'off',
    'onOnce',
    'appendTo',
    'insertAfter',
    'insertBefore',
    'isAttachedTo',
    'replaceElement',
    'restoreElement',
    'destroy'
];
  
// These are the allowed props
const allowedProps = Object.keys(OptionTypes).filter(key => !filteredKeys.includes(key));

export const propsToElementAttributes = (base = {}, { id, style, className }) => {
    return {
        id: base.id || id,
        className: base.className.split(' ').concat(className).filter(n => n).join(' '),
        style: Object.assign({}, base.style || {}, style || {})
    };
}

const getOptionsFromProps = (props, filter) => Object
    .keys(props)
    .filter(prop => filter ? allowedProps.find(key => key.toLowerCase() === prop.toLowerCase()) : true)
    .reduce((options, key) => {
        options[/^on/.test(key) ? key.toLocaleLowerCase() : key] = props[key];
        return options;
    }, {});

export const updateDoka = (instance, props) => instance.setOptions(getOptionsFromProps(props, true));

// Create a new Doka instance based on supplied props and root element
export const createDoka = (component, root, props = {}) => {

    if (!isSupported) return;

    // method hook
    component.doka = {};

    // params will be passed to the Doka create method
    const params = [getOptionsFromProps(props, root !== null)];

    // prepend the root element if it was supplied
    if (root) {
        params.unshift(root);
    }

    // create our Doka instance
    const instance = create(...params);

    // reference Doka methods to component instance (they are placed on the `doka` property to prevent collisions)
    Object.keys(instance)
        .filter(key => !filteredMethods.includes(key))
        .forEach(key => component.doka[key] = instance[key]);
    
    return instance;
}