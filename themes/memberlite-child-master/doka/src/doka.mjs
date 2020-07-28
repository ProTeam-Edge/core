/*!
 * Doka 6.10.1
 * Copyright 2019 PQINA Inc - All Rights Reserved
 * Please visit https://pqina.nl/doka/ for further information
 */
/* eslint-disable */


const isNode = value => value instanceof HTMLElement;

const insertBefore = (newNode, referenceNode) => referenceNode.parentNode.insertBefore(newNode, referenceNode);

const insertAfter = (newNode, referenceNode) => {
    return referenceNode.parentNode.insertBefore(
        newNode,
        referenceNode.nextSibling
    );
};

const isObject = value => typeof value === 'object' && value !== null;

const createStore = (initialState, queries = [], actions = []) => {
    // internal state
    const state = {
        ...initialState
    };

    // contains all actions for next frame, is clear when actions are requested
    const actionQueue = [];
    const dispatchQueue = [];

    // returns a duplicate of the current state
    const getState = () => ({ ...state });

    // returns a duplicate of the actions array and clears the actions array
    const processActionQueue = () => {
        // create copy of actions queue
        const queue = [...actionQueue];
        
        // clear actions queue (we don't want no double actions)
        actionQueue.length = 0;

        return queue;
    };

    // processes actions that might block the main UI thread
    const processDispatchQueue = () => {
        // create copy of actions queue
        const queue = [...dispatchQueue];

        // clear actions queue (we don't want no double actions)
        dispatchQueue.length = 0;

        // now dispatch these actions
        queue.forEach(({ type, data }) => {
            dispatch(type, data);
        });
    };

    // adds a new action, calls its handler and
    const dispatch = (type, data, isBlocking) => {

        // is blocking action
        if (isBlocking) {
            dispatchQueue.push({
                type,
                data
            });
            return;
        }

        // if this action has a handler, handle the action
        if (actionHandlers[type]) {
            actionHandlers[type](data);
        }

        // now add action
        actionQueue.push({
            type,
            data
        });
    };

    const query = (str, ...args) =>
        queryHandles[str] ? queryHandles[str](...args) : null;

    const api = {
        getState,
        processActionQueue,
        processDispatchQueue,
        dispatch,
        query
    };

    let queryHandles = {};
    queries.forEach(query => {
        queryHandles = {
            ...query(state),
            ...queryHandles
        };
    });

    let actionHandlers = {};
    actions.forEach(action => {
        actionHandlers = {
            ...action(dispatch, query, state),
            ...actionHandlers
        };
    });

    return api;
};

const defineProperty = (obj, property, definition) => {
    if (typeof definition === 'function') {
        obj[property] = definition;
        return;
    }
    Object.defineProperty(obj, property, definition);
};

const forin = (obj, cb) => {
    for (const key in obj) {
        // eslint-disable-next-line no-prototype-builtins
        if (!obj.hasOwnProperty(key)) continue;
        cb(key, obj[key]);
    }
};

const createObject = definition => {
    const obj = {};
    forin(definition, property => {
        defineProperty(obj, property, definition[property]);
    });
    return obj;
};

const attr = (node, name, value = null) => {
    if (value === null) {
        return node.getAttribute(name) || node.hasAttribute(name);
    }
    node.setAttribute(name, value);
};

const ns = 'http://www.w3.org/2000/svg';
const svgElements = ['svg', 'path']; // only svg elements used

const isSVGElement = tag => svgElements.includes(tag);

const createElement = (tag, className, attributes = {}) => {
    if (typeof className === 'object') {
        attributes = className;
        className = null;
    }
    const element = isSVGElement(tag)
        ? document.createElementNS(ns, tag)
        : document.createElement(tag);
    if (className) {
        if (isSVGElement(tag)) {
            attr(element, 'class', className);
        } else {
            element.className = className;
        }
    }
    forin(attributes, (name, value) => {
        attr(element, name, value);
    });
    return element;
};

const appendChild = parent => (child, index) => {
    if (typeof index !== 'undefined' && parent.children[index]) {
        parent.insertBefore(child, parent.children[index]);
    } else {
        parent.appendChild(child);
    }
};

const appendChildView = (parent, childViews) => (view, index) => {
    
    if (typeof index !== 'undefined') {
        childViews.splice(index, 0, view);
    } else {
        childViews.push(view);
    }
    
    return view;
};

const removeChildView = (parent, childViews) => view => {
    // remove from child views
    const removedView = childViews.splice(childViews.indexOf(view), 1);

    // clean up view and its children
    if (removedView.length) {
        removedView[0]._destroy();
    }

    // remove the element
    if (view.element.parentNode) {
        parent.removeChild(view.element);
    }

    return view;
};

const getViewRect = (elementRect, childViews, offset, scale) => {
    const left = offset[0] || elementRect.left;
    const top = offset[1] || elementRect.top;
    const right = left + elementRect.width;
    const bottom = top + elementRect.height * (scale[1] || 1);

    const rect = {
        // the rectangle of the element itself
        element: {
            ...elementRect
        },

        // the rectangle of the element expanded to contain its children, does not include any margins
        inner: {
            left: elementRect.left,
            top: elementRect.top,
            right: elementRect.right,
            bottom: elementRect.bottom
        },

        // the rectangle of the element expanded to contain its children including own margin and child margins
        // margins will be added after we've recalculated the size
        outer: {
            left,
            top,
            right,
            bottom
        }
    };

    // expand rect to fit all child rectangles
    childViews
        .filter(childView => !childView.isRectIgnored())
        .map(childView => childView.rect)
        .forEach(childViewRect => {
            expandRect(rect.inner, { ...childViewRect.inner });
            expandRect(rect.outer, { ...childViewRect.outer });
        });

    // calculate inner width and height
    calculateRectSize(rect.inner);

    // append additional margin (top and left margins are included in top and left automatically)
    rect.outer.bottom += rect.element.marginBottom;
    rect.outer.right += rect.element.marginRight;

    // calculate outer width and height
    calculateRectSize(rect.outer);

    return rect;
};

const expandRect = (parent, child) => {
    // adjust for parent offset
    child.top += parent.top;
    child.right += parent.left;
    child.bottom += parent.top;
    child.left += parent.left;

    if (child.bottom > parent.bottom) {
        parent.bottom = child.bottom;
    }

    if (child.right > parent.right) {
        parent.right = child.right;
    }
};

const calculateRectSize = rect => {
    rect.width = rect.right - rect.left;
    rect.height = rect.bottom - rect.top;
};

const isNumber = value => typeof value === 'number';

/**
 * Determines if position is at destination
 * @param position
 * @param destination
 * @param velocity
 * @param errorMargin
 * @returns {boolean}
 */
const thereYet = (position, destination, velocity, errorMargin = 0.001) => {
    return (
        Math.abs(position - destination) < errorMargin &&
        Math.abs(velocity) < errorMargin
    );
};

/**
 * Spring animation
 */
const spring =
    // default options
    ({ stiffness = 0.5, damping = 0.75, mass = 10, delay = 0 } = {}) =>
        // method definition
        {
            let target = null;
            let position = null;
            let velocity = 0;
            let resting = false;
            let delayOffset = null;
            
            // updates spring state
            const interpolate = (ts) => {

                // set start of animation
                if (delayOffset === null) {
                    delayOffset = ts;
                }

                // test if should delay
                if (ts - delay < delayOffset) {
                    return;
                }

                // in rest, don't animate
                if (resting) {
                    return;
                }

                // need at least a target or position to do springy things
                if (!(isNumber(target) && isNumber(position))) {
                    resting = true;
                    velocity = 0;
                    return;
                }

                // calculate spring force
                const f = -(position - target) * stiffness;

                // update velocity by adding force based on mass
                velocity += f / mass;

                // update position by adding velocity
                position += velocity;

                // slow down based on amount of damping
                velocity *= damping;

                // we've arrived if we're near target and our velocity is near zero
                if (thereYet(position, target, velocity)) {
                    position = target;
                    velocity = 0;
                    resting = true;

                    // we done
                    api.onupdate(position);
                    api.oncomplete(position);
                } else {
                    // progress update
                    api.onupdate(position);
                }
            };

            /**
             * Set new target value
             * @param value
             */
            const setTarget = value => {

                // if currently has no position, set target and position to this value
                if (isNumber(value) && !isNumber(position)) {
                    position = value;
                    delayOffset = null;
                }

                // next target value will not be animated to
                if (target === null) {
                    target = value;
                    position = value;
                    delayOffset = null;
                }

                // is already resting, enable delay
                if (resting) {
                    delayOffset = null;
                }

                // let start moving to target
                target = value;

                // already at target
                if (position === target || typeof target === 'undefined') {

                    // now resting as target is current position, stop moving
                    resting = true;
                    velocity = 0;
                    delayOffset = null;

                    // done!
                    api.onupdate(position);
                    api.oncomplete(position);

                    return;
                }

                resting = false;
            };

            // need 'api' to call onupdate callback
            const api = createObject({
                interpolate,
                target: {
                    set: setTarget,
                    get: () => target
                },
                resting: {
                    get: () => resting
                },
                onupdate: () => {},
                oncomplete: () => {},
                position: {
                    get: () => position
                }
            });

            return api;
        };

const easeInOutQuad = t => (t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t);

const tween =
    // default values
    ({ duration = 500, easing = easeInOutQuad, delay = 0 } = {}) =>
        // method definition
        {
            let start = null;
            let t;
            let p;
            let resting = true;
            let reverse = false;
            let target = null;

            const interpolate = ts => {
                if (resting || target === null) {
                    return;
                }

                if (start === null) {
                    start = ts;
                }

                if (ts - start < delay) {
                    return;
                }

                t = ts - start - delay;

                if (t < duration) {
                    p = t / duration;
                    api.onupdate(
                        (t >= 0 ? easing(reverse ? 1 - p : p) : 0) * target
                    );
                } else {
                    t = 1;
                    p = reverse ? 0 : 1;
                    api.onupdate(p * target);
                    api.oncomplete(p * target);
                    resting = true;
                }
            };

            // need 'api' to call onupdate callback
            const api = createObject({
                interpolate,
                target: {
                    get: () => (reverse ? 0 : target),
                    set: value => {
                        // is initial value
                        if (target === null) {
                            target = value;
                            api.onupdate(value);
                            api.oncomplete(value);
                            return;
                        }

                        // want to tween to a smaller value and have a current value
                        if (value < target) {
                            target = 1;
                            reverse = true;
                        } else {
                            // not tweening to a smaller value
                            reverse = false;
                            target = value;
                        }

                        // let's go!
                        resting = false;
                        start = null;
                    }
                },
                resting: {
                    get: () => resting
                },
                onupdate: () => {},
                oncomplete: () => {}
            });

            return api;
        };

const animator = {
    spring,
    tween
};

/*
 { type: 'spring', stiffness: .5, damping: .75, mass: 10, delay:0 };
 { translation: { type: 'spring', ... }, ... }
 { translation: { x: { type: 'spring', ... } } }
*/
const createAnimator = (definition, category, property) => {
    // default is single definition
    // we check if transform is set, if so, we check if property is set
    const def =
        definition[category] &&
        typeof definition[category][property] === 'object'
            ? definition[category][property]
            : definition[category] || definition;

    const type = typeof def === 'string' ? def : def.type;
    const props = typeof def === 'object' ? { ...def } : {};

    return animator[type] ? animator[type](props) : null;
};

const addGetSet = (keys, obj, props, overwrite = false) => {
    obj = Array.isArray(obj) ? obj : [obj];
    obj.forEach(o => {
        keys.forEach(key => {
            let name = key;
            let getter = () => props[key];
            let setter = value => (props[key] = value);

            if (typeof key === 'object') {
                name = key.key;
                getter = key.getter || getter;
                setter = key.setter || setter;
            }

            if (o[name] && !overwrite) {
                return;
            }

            o[name] = {
                get: getter,
                set: setter
            };
        });
    });
};

// add to state,
// add getters and setters to internal and external api (if not set)
// setup animators

const animations = ({
    mixinConfig,
    viewProps,
    viewInternalAPI,
    viewExternalAPI
}) => {
    // initial properties
    const initialProps = { ...viewProps };

    // list of all active animations
    const animations = [];

    // setup animators
    forin(mixinConfig, (property, animation) => {
        const animator = createAnimator(animation);
        if (!animator) {
            return;
        }

        // when the animator updates, update the view state value
        animator.onupdate = value => {
            viewProps[property] = value;
        };

        // set animator target
        animator.target = initialProps[property];

        // when value is set, set the animator target value
        const prop = {
            key: property,
            setter: value => {

                // if already at target, we done!
                if (animator.target === value) {
                    return;
                }

                animator.target = value;
            },
            getter: () => viewProps[property]
        };

        // add getters and setters
        addGetSet([prop], [viewInternalAPI, viewExternalAPI], viewProps, true);

        // add it to the list for easy updating from the _write method
        animations.push(animator);
    });

    // expose internal write api
    return {
        write: ts => {
            let resting = true;
            animations.forEach(animation => {
                if (!animation.resting) {
                    resting = false;
                }
                animation.interpolate(ts);
            });
            return resting;
        },
        destroy: () => {}
    };
};

const addEvent = element => (type, fn) => {
    element.addEventListener(type, fn);
};

const removeEvent = element => (type, fn) => {
    element.removeEventListener(type, fn);
};

// mixin
const listeners = ({
    viewExternalAPI,
    view
}) => {
    const events = [];

    const add = addEvent(view.element);
    const remove = removeEvent(view.element);

    viewExternalAPI.on = (type, fn) => {
        events.push({
            type,
            fn
        });
        add(type, fn);
    };

    viewExternalAPI.off = (type, fn) => {
        events.splice(
            events.findIndex(event => event.type === type && event.fn === fn),
            1
        );
        remove(type, fn);
    };

    return {
        write: () => {
            // not busy
            return true;
        },
        destroy: () => {
            events.forEach(event => {
                remove(event.type, event.fn);
            });
        }
    };
};

// add to external api and link to props

const apis = ({ mixinConfig, viewProps, viewExternalAPI }) => {
    addGetSet(mixinConfig, viewExternalAPI, viewProps);
};

// add to state,
// add getters and setters to internal and external api (if not set)
// set initial state based on props in viewProps
// apply as transforms each frame

const defaults = {
    opacity: 1,
    scaleX: 1,
    scaleY: 1,
    translateX: 0,
    translateY: 0,
    rotateX: 0,
    rotateY: 0,
    rotateZ: 0,
    originX: 0,
    originY: 0
};

const styles = ({
    mixinConfig,
    viewProps,
    viewInternalAPI,
    viewExternalAPI,
    view
}) => {
    // initial props
    const initialProps = { ...viewProps };

    // current props
    const currentProps = {};

    // we will add those properties to the external API and link them to the viewState
    addGetSet(mixinConfig, [viewInternalAPI, viewExternalAPI], viewProps);

    // override rect on internal and external rect getter so it takes in account transforms
    const getOffset = () => [
        viewProps['translateX'] || 0,
        viewProps['translateY'] || 0
    ];
    const getScale = () => [viewProps['scaleX'] || 0, viewProps['scaleY'] || 0];
    const getRect = () =>
        view.rect
            ? getViewRect(view.rect, view.childViews, getOffset(), getScale())
            : null;
    viewInternalAPI.rect = { get: getRect };
    viewExternalAPI.rect = { get: getRect };

    // apply view props
    mixinConfig.forEach(key => {
        viewProps[key] =
            typeof initialProps[key] === 'undefined'
                ? defaults[key]
                : initialProps[key];
    });

    // expose api
    return {
        write: () => {

            // see if props have changed
            if (!propsHaveChanged(currentProps, viewProps)) {
                return;
            }

            // moves element to correct position on screen
            applyStyles(view.element, viewProps);

            // store new transforms
            Object.assign(currentProps, {...viewProps});

            // no longer busy
            return true;
        },
        destroy: () => {}
    };
};

const propsHaveChanged = (currentProps, newProps) => {
    // different amount of keys
    if (Object.keys(currentProps).length !== Object.keys(newProps).length) {
        return true;
    }

    // lets analyze the individual props
    for (const prop in newProps) {
        if (newProps[prop] !== currentProps[prop]) {
            return true;
        }
    }

    return false;
};

const applyStyles = (
    element,
    {
        opacity,
        perspective,
        translateX,
        translateY,
        scaleX,
        scaleY,
        rotateX,
        rotateY,
        rotateZ,
        originX,
        originY,
        width,
        height
    }
) => {
    
    let transforms = '';
    let styles = '';

    // handle transform origin
    if (originX != null || originY != null) {
        styles += `transform-origin: ${originX || 0}px ${originY || 0}px;`;
    }

    // transform order is relevant
    // 0. perspective
    if (perspective != null) {
        transforms += `perspective(${perspective}px) `;
    }

    // 1. translate
    if (translateX != null || translateY != null) {
        transforms += `translate3d(${translateX || 0}px, ${translateY || 0}px, 0) `;
    }

    // 2. scale
    if (scaleX != null || scaleY != null) {
        transforms += `scale3d(${scaleX != null ? scaleX : 1}, ${
            scaleY != null ? scaleY : 1
        }, 1) `;
    }

    // 3. rotate
    if (rotateZ != null) {
        transforms += `rotateZ(${rotateZ}rad) `;
    }

    if (rotateX != null) {
        transforms += `rotateX(${rotateX}rad) `;
    }

    if (rotateY != null) {
        transforms += `rotateY(${rotateY}rad) `;
    }

    // add transforms
    if (transforms != '') {
        styles += `transform:${transforms};`;
    }

    // add opacity
    if (opacity != null) {
        styles += `opacity:${opacity};`;

        // if we're below 100% opacity this element can't be clicked
        if (opacity < 1) {
            styles += `pointer-events:none;`;
        }

        // if we reach zero, we make the element inaccessible for tabbing
        if (opacity === 0 && element.nodeName === 'BUTTON') {
            styles += `visibility:hidden;`;
        }
        
    }

    // add width
    if (width != null) {
        styles += `width:${width}px;`;
    }

    // add height
    if (height != null) {
        styles += `height:${height}px;`;
    }

    // apply styles
    const elementCurrentStyle = element.elementCurrentStyle || '';

    // if new styles does not match current styles, lets update!
    if (
        styles.length !== elementCurrentStyle.length ||
        styles !== elementCurrentStyle
    ) {
        element.style.cssText = styles;
        // store current styles so we can compare them to new styles later on
        // _not_ getting the style attribute is faster
        element.elementCurrentStyle = styles;
    }
};

const Mixins = {
    styles,
    listeners,
    animations,
    apis
};

const updateRect = (rect = {}, element = {}, style = {}) => {

    if (!element.layoutCalculated) {
        rect.paddingTop = parseInt(style.paddingTop, 10) || 0;
        rect.marginTop = parseInt(style.marginTop, 10) || 0;
        rect.marginRight = parseInt(style.marginRight, 10) || 0;
        rect.marginBottom = parseInt(style.marginBottom, 10) || 0;
        rect.marginLeft = parseInt(style.marginLeft, 10) || 0;
        element.layoutCalculated = true;
    }

    rect.left = element.offsetLeft || 0;
    rect.top = element.offsetTop || 0;
    rect.width = element.offsetWidth || 0;
    rect.height = element.offsetHeight || 0;

    rect.right = rect.left + rect.width;
    rect.bottom = rect.top + rect.height;

    rect.scrollTop = element.scrollTop;

    rect.hidden = element.offsetParent === null && style.position !== 'fixed';

    return rect;
};

const IS_BROWSER = (() => typeof window !== 'undefined' && typeof window.document !== 'undefined')();
const isBrowser = () => IS_BROWSER;

const testElement = isBrowser() ? createElement('svg') : {};
const getChildCount = 'children' in testElement ? el => el.children.length : el => el.childNodes.length;

const createView =
    // default view definition
    ({
        // element definition
        tag = 'div',
        name = null,
        attributes = {},

        // view interaction
        read = () => {},
        write = () => {},
        create = () => {},
        destroy = () => {},

        // hooks
        filterFrameActionsForChild = (child, actions) => actions,
        didCreateView = () => {},
        didWriteView = () => {},
        shouldUpdateChildViews = () => true,

        // rect related
        ignoreRect = false,
        ignoreRectUpdate = false,

        // mixins
        mixins = []
    } = {}) => (
        // each view requires reference to store
        store,
        // specific properties for this view
        props = {}
    ) => {
            // root element should not be changed
            const element = createElement(tag, name ? `doka--${name}` : null, attributes);

            // style reference should also not be changed
            const style = window.getComputedStyle(element, null);

            // element rectangle
            const rect = updateRect();
            let frameRect = null;

            // rest state
            let isResting = false;

            // pretty self explanatory
            const childViews = [];

            // loaded mixins
            const activeMixins = [];

            // references to created children
            const ref = {};

            // state used for each instance
            const state = {};

            // list of writers that will be called to update this view
            const writers = [
                write // default writer
            ];

            const readers = [
                read // default reader
            ];

            const destroyers = [
                destroy // default destroy
            ];

            // core view methods
            const getElement = () => element;
            const getChildViews = () => [...childViews];
            const getReference = () => ref;
            const createChildView = store => (view, props) => view(store, props);
            const getRect = () => {
                if (frameRect) {
                    return frameRect;
                }
                frameRect = getViewRect(rect, childViews, [0, 0], [1, 1]);
                return frameRect;
            };
            const getStyle = () => style;

            /**
             * Read data from DOM
             * @private
             */
            const _read = () => {

                frameRect = null;

                // read child views
                if (shouldUpdateChildViews({ root: internalAPI, props })) {
                    childViews.forEach(child => child._read());
                }

                const shouldUpdate = !(ignoreRectUpdate && rect.width && rect.height);
                if (shouldUpdate) {
                    updateRect(rect, element, style);
                }

                // readers
                const api = { root: internalAPI, props, rect };
                readers.forEach(reader =>
                    reader(api)
                );

            };

            /**
             * Write data to DOM
             * @private
             */
            const _write = (ts, frameActions = []) => {

                // if no actions, we assume that the view is resting
                let resting = frameActions.length === 0;

                // writers
                writers.forEach(writer => {
                    const writerResting = writer({
                        props,
                        root: internalAPI,
                        actions: frameActions,
                        timestamp: ts
                    });
                    if (writerResting === false) {
                        resting = false;
                    }
                });

                // run mixins
                activeMixins.forEach(mixin => {
                    // if one of the mixins is still busy after write operation, we are not resting
                    const mixinResting = mixin.write(ts);
                    if (mixinResting === false) {
                        resting = false;
                    }
                });

                if (shouldUpdateChildViews({
                    props,
                    root: internalAPI,
                    actions: frameActions,
                    timestamp: ts
                })) {
                        
                    // updates child views that are currently attached to the DOM
                    childViews
                        .filter(child => !!child.element.parentNode)
                        .forEach(child => {
                            // if a child view is not resting, we are not resting
                            const childResting = child._write(
                                ts,
                                filterFrameActionsForChild(child, frameActions)
                            );
                            if (!childResting) {
                                resting = false;
                            }
                        });

                    // append new elements to DOM and update those
                    childViews
                        //.filter(child => !child.element.parentNode)
                        .forEach((child, index) => {

                            // skip 
                            if (child.element.parentNode) {
                                return;
                            }

                            // append to DOM
                            internalAPI.appendChild(child.element, index);

                            // call read (need to know the size of these elements)
                            child._read();

                            // re-call write
                            child._write(
                                ts,
                                filterFrameActionsForChild(child, frameActions)
                            );

                            // we just added somthing to the dom, no rest
                            resting = false;
                        });

                }
                
                // update resting state
                isResting = resting;

                didWriteView({
                    props,
                    root: internalAPI,
                    actions: frameActions,
                    timestamp: ts
                });
                
                // let parent know if we are resting
                return resting;
            };

            const _destroy = () => {
                activeMixins.forEach(mixin => mixin.destroy());
                destroyers.forEach(destroyer => { destroyer({ root: internalAPI });});
                childViews.forEach(child => child._destroy());
            };

            const invalidateLayout = () => element.layoutCalculated = false;

            // sharedAPI
            const sharedAPIDefinition = {
                element: {
                    get: getElement
                },
                style: {
                    get: getStyle
                },
                childViews: {
                    get: getChildViews
                }
            };

            // private API definition
            const internalAPIDefinition = {
                ...sharedAPIDefinition,
                rect: {
                    get: getRect
                },

                // access to custom children references
                ref: {
                    get: getReference
                },

                // dom modifiers
                is: needle => name === needle,
                appendChild: appendChild(element),
                createChildView: createChildView(store),
                linkView: view => { childViews.push(view); return view },
                unlinkView: view => { childViews.splice(childViews.indexOf(view), 1); },
                appendChildView: appendChildView(element, childViews),
                removeChildView: removeChildView(element, childViews),
                registerWriter: writer => writers.push(writer),
                registerReader: reader => readers.push(reader),
                registerDestroyer: destroyer => destroyers.push(destroyer),
                invalidateLayout,

                // access to data store
                dispatch: store.dispatch,
                query: store.query
            };

            // public view API methods
            const externalAPIDefinition = {
                element: {
                    get: getElement
                },
                childViews: {
                    get: getChildViews
                },
                rect: {
                    get: getRect
                },
                resting: {
                    get: () => isResting
                },
                isRectIgnored: () => ignoreRect,
                invalidateLayout,
                _read,
                _write,
                _destroy
            };

            // mixin API methods
            const mixinAPIDefinition = {
                ...sharedAPIDefinition,
                rect: {
                    get: () => rect
                }
            };

            // add mixin functionality
            Object.keys(mixins).sort((a,b) => {
                // move styles to the back of the mixin list (so adjustments of other mixins are applied to the props correctly)
                if (a === 'styles') {
                    return 1;
                }
                else if (b ==='styles') {
                    return -1;
                }
                return 0;
            }).forEach(key => {

                const mixinAPI = Mixins[key]({
                    mixinConfig: mixins[key],
                    viewProps: props,
                    viewState: state,
                    viewInternalAPI: internalAPIDefinition,
                    viewExternalAPI: externalAPIDefinition,
                    view: createObject(mixinAPIDefinition)
                });

                if (mixinAPI) {
                    activeMixins.push(mixinAPI);
                }
            });

            // construct private api
            const internalAPI = createObject(internalAPIDefinition);

            // create the view
            create({
                root: internalAPI,
                props
            });

            // append created child views to root node
            const childCount = getChildCount(element) || 0; // need to know the current child count so appending happens in correct order
            childViews.forEach((child, index) => {
                internalAPI.appendChild(child.element, childCount + index);
            });

            // call did create
            didCreateView(internalAPI);

            // expose public api
            return createObject(externalAPIDefinition, props);
        };

const createPainter = (read, write, fps = 60) => {

    const name = '__framePainter';

    // set global painter
    if (window[name]) {
        window[name].readers.push(read);
        window[name].writers.push(write);
        return;
    }
    
    window[name] = {
        readers:[read],
        writers:[write]
    };

    const painter = window[name];

    const interval = 1000 / fps;
    let last = null;
    let id = null;
    let requestTick = null;
    let cancelTick = null;

    const setTimerType = () => {
        if (document.hidden) {
            requestTick = () => window.setTimeout(() => tick(performance.now()), interval);
            cancelTick = () => window.clearTimeout(id);
        }
        else {
            requestTick = () => window.requestAnimationFrame(tick);
            cancelTick = () => window.cancelAnimationFrame(id);
        }
    };

    document.addEventListener('visibilitychange', () => {
        if (cancelTick) cancelTick();
        setTimerType();
        tick(performance.now());
    });

    const tick = ts => {

        // queue next tick
        id = requestTick(tick);

        // limit fps
        if (!last) {
            last = ts;
        }

        const delta = ts - last;

        if (delta <= interval) {
            // skip frame
            return;
        }

        // align next frame
        last = ts - delta % interval;

        // update view
        painter.readers.forEach(read => read());
        painter.writers.forEach(write => write(ts));
    };

    setTimerType();
    tick(performance.now());

    return {
        pause: () => {
            cancelTick(id);
        }
    };
};

const createRoute = (routes, fn) => ({ root, props, actions = [], timestamp }) => {
    actions.filter(action => routes[action.type])
        .forEach(action =>
            routes[action.type]({ root, props, action: action.data, timestamp })
        );
    if (fn) {
        return fn({ root, props, actions, timestamp });
    }
};

const isArray = value => Array.isArray(value);

const isEmpty = value => value == null;

const trim = str => str.trim();

const toString = value => '' + value;

const toArray = (value, splitter = ',') => {
    if (isEmpty(value)) {
        return [];
    }
    if (isArray(value)) {
        return value;
    }
    return toString(value)
        .split(splitter)
        .map(trim)
        .filter(str => str.length);
};

const isBoolean = value => typeof value === 'boolean';

const toBoolean = value => (isBoolean(value) ? value : value === 'true');

const isString = value => typeof value === 'string';

const toNumber = value =>
    isNumber(value)
        ? value
        : isString(value) ? toString(value).replace(/[a-z]+/gi, '') : 0;

const toInt = value => parseInt(toNumber(value), 10);

const toFloat = value => parseFloat(toNumber(value));

const isInt = value =>
    isNumber(value) && isFinite(value) && Math.floor(value) === value;

const toBytes = value => {
    // is in bytes
    if (isInt(value)) {
        return value;
    }

    // is natural file size
    let naturalFileSize = toString(value).trim();

    // if is value in megabytes
    if (/MB$/i.test(naturalFileSize)) {
        naturalFileSize = naturalFileSize.replace(/MB$i/, '').trim();
        return toInt(naturalFileSize) * 1000 * 1000;
    }

    // if is value in kilobytes
    if (/KB/i.test(naturalFileSize)) {
        naturalFileSize = naturalFileSize.replace(/KB$i/, '').trim();
        return toInt(naturalFileSize) * 1000;
    }

    return toInt(naturalFileSize);
};

const isFunction = value => typeof value === 'function';

const toFunctionReference = string => {
    let ref = self;
    let levels = string.split('.');
    let level = null;
    while ((level = levels.shift())) {
        ref = ref[level];
        if (!ref) {
            return null;
        }
    }
    return ref;
};

const isNull = value => value === null;

const getType = value => {
    if (isArray(value)) {
        return 'array';
    }

    if (isNull(value)) {
        return 'null';
    }

    if (isInt(value)) {
        return 'int';
    }

    if (/^[0-9]+ ?(?:GB|MB|KB)$/gi.test(value)) {
        return 'bytes';
    }

    return typeof value;
};

const replaceSingleQuotes = str => str
    .replace(/{\s*'/g,'{"')
    .replace(/'\s*}/g,'"}')
    .replace(/'\s*:/g,'":')
    .replace(/:\s*'/g,':"')
    .replace(/,\s*'/g,',"')
    .replace(/'\s*,/g,'",');

const conversionTable = {
    array: toArray,
    boolean: toBoolean,
    int: value => getType(value) === 'bytes' ? toBytes(value) : toInt(value),
    float: toFloat,
    bytes: toBytes,
    string: value => isFunction(value) ? value : toString(value),
    object: value => {
        try {
            return JSON.parse(replaceSingleQuotes(value))
        }
        catch(e) {
            return value;
        }
    },
    file: value => {
        return value;
    },
    function: value => toFunctionReference(value)
};

const convertTo = (value, type) => conversionTable[type](value);

const getValueByType = (newValue, defaultValue, valueType) => {
    // can always assign default value
    if (newValue === defaultValue) {
        return newValue;
    }

    // get the type of the new value
    let newValueType = getType(newValue);

    // is valid type?
    if (newValueType !== valueType) {
        // is string input, let's attempt to convert
        const convertedValue = convertTo(newValue, valueType);

        // what is the type now
        newValueType = getType(convertedValue);

        // no valid conversions found
        if (convertedValue === null) {
            throw `Trying to assign value with incorrect type, allowed type: "${valueType}"`;
        } else {
            newValue = convertedValue;
        }
    }

    // assign new value
    return newValue;
};

const createOption = (defaultValue, valueType) => {
    let currentValue = defaultValue;
    return {
        enumerable: true,
        get: () => currentValue,
        set: newValue => {
            currentValue = getValueByType(newValue, defaultValue, valueType);
        }
    };
};

const createOptions = options => {
    const obj = {};
    forin(options, key => {

        const correctedKey = isString(options[key]) ? options[key] : key;
        const optionDefinition = options[correctedKey];

        // point key to corrected key in options object
        if (correctedKey !== key) {
            obj[key] = obj[correctedKey];
            return;
        }
        
        // create new option
        obj[key] = createOption(
            optionDefinition[0],
            optionDefinition[1]
        );
    });
    return createObject(obj);
};

const resetState = state => {
    
    // file data
    /*
    file: {
        orientation: -1,
        data: null,
        preview: null,
        thumb: null
    } 
    */

    state.file = null;

    // current active view id
    state.activeView = null;

    // markup to draw on picture
    state.markup = [];
    state.markupToolValues = {};

    // root information
    state.rootRect = {
        x: 0,
        y: 0,
        left: 0,
        top: 0,
        width: 0,
        height: 0
    };

    // stage information
    state.stage = null;
    state.stageOffset = null;

    // image information
    state.image = null;

    // zoom timeout
    state.zoomTimeoutId = null,

    // TODO: replace with better solution, this updates view immidiately without animations
    state.instantUpdate = false;

    // set with success and failure state for loadImage call
    state.filePromise = null;

    // file loader
    state.fileLoader = null;

    // instructions received when loading the file
    state.instructions = {
        size: null,
        crop: null,
        filter: null,
        color: null
    },

    // active matrices
    state.filter = null;
    state.filterName = null;
    state.filterValue = null; // always null for now
    state.colorValues = {};
    state.colorMatrices = {};

    // size view data
    state.size = {
        width: false, // false (no data entered), null (cleared), int (data entered)
        height: false, // false, null, int
        aspectRatioLocked: true,
        aspectRatioPrevious: false // null, false, int
    };

    // crop view data
    state.crop = {

        // current view state
        rectangle: null,
        transforms: null,
        rotation: null,
        flip: null,
        aspectRatio: null,

        // interactions
        isRotating: false,
        isDirty: false,

        // limitations
        limitToImageBounds: true,

        // the rectangle and image data while modifying
        draft: {
            rectangle: null,
            transforms: null
        }
    };

};

const createInitialState = options => {

    const state = {

        // timeout to wait for first image
        noImageTimeout: null,

        // default options
        options: createOptions(options)
        
    };

    resetState(state);

    return state;
};

const fromCamels = (string, separator = '-') =>
    string
        .split(/(?=[A-Z])/)
        .map(part => part.toLowerCase())
        .join(separator);

const createOptionAPI = (store, options) => {
    const obj = {};
    forin(options, key => {
        const correctedKey = isString(options[key]) ? options[key] : key;
        obj[key] = {
            get: () => {
                return store.getState().options[correctedKey];
            },
            set: value => {
                store.dispatch(`SET_${fromCamels(correctedKey, '_').toUpperCase()}`, {
                    value
                });
            }
        };
    });
    return obj;
};

const createOptionActions = options => (dispatch, query, state) => {
    const obj = {};
    forin(options, key => {
        const name = fromCamels(key, '_').toUpperCase();
        obj[`SET_${name}`] = action => {
            let prevValue;
            try {
                prevValue = state.options[key];
                state.options[key] = action.value;
            } catch (e) {
                // nope, failed
            }
            // we successfully set the value of this option
            dispatch(`DID_SET_${name}`, { value: state.options[key], prevValue });
        };
    });
    return obj;
};

const createOptionQueries = options => state => {
    const obj = {};
    forin(options, key => {
        obj[`GET_${fromCamels(key, '_').toUpperCase()}`] = () => state.options[key];
    });
    return obj;
};

const getUniqueId = () =>
    Math.random()
        .toString(36)
        .substr(2, 9);

const arrayRemove = (arr, index) => arr.splice(index, 1);

const on = () => {
    const listeners = [];
    const off = (event, cb) => {
        arrayRemove(
            listeners,
            listeners.findIndex(
                listener =>
                    listener.event === event && (listener.cb === cb || !cb)
            )
        );
    };
    return {
        fire: (event, ...args) => {
                listeners
                    .filter(listener => listener.event === event)
                    .map(listener => listener.cb)
                    .forEach(cb => {
                        setTimeout(() => {
                            cb(...args);
                        }, 0);
                    });
        },
        on: (event, cb) => {
            listeners.push({ event, cb });
        },
        onOnce: (event, cb) => {
            listeners.push({
                event,
                cb: (...args) => {
                    off(event, cb);
                    cb(...args);
                }
            });
        },
        off
    };
};

const Type = {
    BOOLEAN: 'boolean',
    INT: 'int',
    NUMBER: 'number',
    STRING: 'string',
    ARRAY: 'array',
    OBJECT: 'object',
    FUNCTION: 'function',
    FILE: 'file'
};

let testResult = null;
const isIOS = () => {
    if (testResult === null) {
        testResult = (/iPad|iPhone|iPod/.test(navigator.userAgent) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) && !window.MSStream;
    }
    return testResult;
};

const getOptions = () => ({
    ...defaultOptions
});

const setOptions = opts => {
    forin(opts, (key, value) => {
        // key does not exist, so this option cannot be set
        if (!defaultOptions[key]) return;
        setOption(key, value);
    });
};

const correctDeprecatedOption = (key) => isString(defaultOptions[key]) ? defaultOptions[key] : key;

const setOption = (key, value) => {
    key = correctDeprecatedOption(key);
    defaultOptions[key][0] = getValueByType(value, defaultOptions[key][0], defaultOptions[key][1]);
};

// default options on app
const defaultOptions = {

    // the id to add to the root element
    id: [null, Type.STRING],

    // the classname to put the root element
    className: [null, Type.STRING],

    // the image source to load
    src: [null, Type.FILE],

    // name to use for localstorage
    storageName: ['doka', Type.STRING],

    // max image width
    maxImagePreviewWidth: [1500, Type.INT],
    maxImagePreviewHeight: [1500, Type.INT],
    
    // image preview size
    imagePreviewScaleMode: ['stage', Type.STRING],
    // 'stage' => use stage size as preview size
    // 'crop' => use current crop size as preview size
    // 'image' => use input image size as preview size
 
    // allow fit preview to stage size
    allowPreviewFitToView: [true, Type.BOOLEAN],

    // toggle cancel and confirm buttons
    allowButtonCancel: [true, Type.BOOLEAN],
    allowButtonConfirm: [true, Type.BOOLEAN],
    allowButtonReset: [true, Type.BOOLEAN],

    // drop files on editor
    allowDropFiles: [false, Type.BOOLEAN],
    allowBrowseFiles: [true, Type.BOOLEAN],

    // automatically close when in overlay or modal mode
    allowAutoClose: [true, Type.BOOLEAN],

    // automatically destroy when closed in overlay or modal mode
    allowAutoDestroy: [false, Type.BOOLEAN],

    // default utils stack
    utils: [['crop', 'filter', 'color', 'markup'], Type.ARRAY],

    // util to show
    util: [null, Type.STRING],

    // the initial state of the app
    initialState: [null, Type.OBJECT],

    // output modifiers
    outputData: [false, Type.BOOLEAN],
    outputFile: [true, Type.BOOLEAN],
    outputCorrectImageExifOrientation: [true, Type.BOOLEAN],
    outputStripImageHead: [true, Type.BOOLEAN], // will remove EXIF information from image/jpeg
    outputType: [null, Type.STRING],            // image/jpeg or image/png
    outputQuality: [null, Type.INT],            // 0 to 100

    // auto size auto resize settings
    // - force => force set size
    // - cover => pick biggest dimension
    // - contain => pick smaller dimension
    outputFit: ['cover', Type.STRING],
    outputUpscale: [true, Type.BOOLEAN],
    outputWidth: [null, Type.INT],
    outputHeight: [null, Type.INT],
    outputCanvasBackgroundColor: [null, Type.STRING],
    outputCanvasMemoryLimit: [isBrowser() && isIOS() ? 4096 * 4096 : null, Type.INT],
    outputCanvasSyncTarget: [null, Type.OBJECT],

    // output size user supplied resize settings, will override outputWidth and outputHeight
    size: [null, Type.OBJECT],
    sizeMin: [{ width: 1, height: 1 }, Type.OBJECT],
    sizeMax: [{ width: 9999, height: 9999 }, Type.OBJECT],

    //#if !_EXCLUDE_FILTER_UTIL
    // the filter to pre-select
    filter: [null, Type.OBJECT],
    filters: [{
        'original': {
            label: 'Original',
            matrix: () => null
        },
        'chrome': {
            label: 'Chrome',
            matrix: () => [
                1.398,-0.316, 0.065,-0.273, 0.201,
                -0.051, 1.278,-0.080,-0.273, 0.201,
                -0.051, 0.119, 1.151,-0.290, 0.215,
                 0.000, 0.000, 0.000, 1.000, 0.000
            ]
        },
        'fade': {
            label: 'Fade',
            matrix: () => [
                1.073,-0.015, 0.092,-0.115,-0.017,
                0.107, 0.859, 0.184,-0.115,-0.017,
                0.015, 0.077, 1.104,-0.115,-0.017,
                0.000, 0.000, 0.000, 1.000, 0.000
            ]
        },
        'mono': {
            label: 'Mono',
            matrix: () => [
                0.212, 0.715, 0.114, 0.000, 0.000,
                0.212, 0.715, 0.114, 0.000, 0.000,
                0.212, 0.715, 0.114, 0.000, 0.000,
                0.000, 0.000, 0.000, 1.000, 0.000
            ]
        },
        'noir': {
            label: 'Noir',
            matrix: () => [
                0.150, 1.300,-0.250, 0.100,-0.200,
                0.150, 1.300,-0.250, 0.100,-0.200,
                0.150, 1.300,-0.250, 0.100,-0.200,
                0.000, 0.000, 0.000, 1.000, 0.000
            ]
        }
    }, Type.OBJECT],
    //#endif

    // the current crop
    crop: [null, Type.OBJECT],

    // toggles image output size indicator
    cropShowSize: [false, Type.BOOLEAN],

    // timeout till auto zoom
    cropZoomTimeout: [null, Type.INT],

    // crop mask
    cropMask: [null, Type.FUNCTION],
    cropMaskInset: [0, Type.INT],

    // allow adjust crop rectangle
    cropAllowResizeRect: [true, Type.BOOLEAN],

    // toggle rotate buttons
    cropAllowImageTurnLeft: [true, Type.BOOLEAN],
    cropAllowImageTurnRight: [false, Type.BOOLEAN],

    // toggle flip buttons
    cropAllowImageFlipHorizontal: [true, Type.BOOLEAN],
    cropAllowImageFlipVertical: [true, Type.BOOLEAN],

    // toggle letterbox cropping
    cropAllowToggleLimit: [false, Type.BOOLEAN],
    cropLimitToImageBounds: [true, Type.BOOLEAN],

    // toggle zoom instruction
    cropAllowInstructionZoom: [false, Type.BOOLEAN],

    // toggle rotation control
    cropAllowRotate: [true, Type.BOOLEAN],
    
    // scale when zoming out
    cropResizeMatchImageAspectRatio: [false, Type.BOOLEAN],

    // the keycode of the key to use for resizing
    cropResizeKeyCodes: [[18, 91, 92, 93], Type.ARRAY], // Alt, CMD

    // can resize only when scrolling on crop area
    cropResizeScrollRectOnly: [false, Type.BOOLEAN],

    // aspect ratios
    cropAspectRatio: [null, Type.STRING], // the crop aspect ratio to use
    cropAspectRatioOptions: [null, Type.ARRAY], // the options to show
    
    // min crop size
    cropMinImageWidth: [1, Type.INT],
    cropMinImageHeight: [1, Type.INT],

    //#if !_EXCLUDE_COLOR_UTIL
    // color options
    colorBrightness: [0, Type.NUMBER],
    colorBrightnessRange: [[-0.25, .25], Type.ARRAY],
    colorContrast: [1, Type.NUMBER],
    colorContrastRange: [[.5, 1.5], Type.ARRAY],
    colorExposure: [1, Type.NUMBER],
    colorExposureRange: [[.5, 1.5], Type.ARRAY],
    colorSaturation: [1, Type.NUMBER],
    colorSaturationRange: [[0, 2], Type.ARRAY],
    //#endif

    //#if !_EXCLUDE_MARKUP_UTIL
    // markup options
    markup: [null, Type.ARRAY],
    markupUtil: ['select', Type.STRING],
    markupFilter: [() => true, Type.FUNCTION],
    markupAllowAddMarkup: [true, Type.BOOLEAN],
    markupAllowCustomColor: [true, Type.BOOLEAN],
    markupDrawDistance: [4, Type.NUMBER],
    
    markupColor: ['#000', Type.STRING],
    markupColorOptions: [
        [
            // name, color, option color
            ['White', '#fff', '#f6f6f6'],
            ['Silver', '#9e9e9e'],
            ['Black', '#000', '#333'],
            ['Red', '#f44336'],
            ['Orange', '#ff9800'],
            ['Yellow', '#ffeb3b'],
            ['Green', '#4caf50'],
            ['Blue', '#2196f3'],
            ['Violet', '#3f51b5'],
            ['Purple', '#9c27b0']
        ], Type.ARRAY
    ],

    markupFontSize: [.1, Type.NUMBER],
    markupFontSizeOptions: [
        [
            // name, value
            ['XL', .15],
            ['L', .125],
            ['M', .1],
            ['S', .075],
            ['XS', .05]
        ],
        Type.ARRAY
    ],

    markupFontFamily: ['Helvetica, Arial, Verdana', Type.STRING],
    markupFontFamilyOptions: [
        [
            // name, value
            ['Serif', 'Palatino, \'Times New Roman\', serif'],
            ['Sans Serif', 'Helvetica, Arial, Verdana'],
            ['Monospaced', 'Monaco, \'Lucida Console\', monospaced'],
        ],
        Type.ARRAY
    ],

    markupShapeStyle: [[.015, null], Type.ARRAY],
    markupShapeStyleOptions: [
        [
            // name, stroke width, dash style, option stroke width
            ['Fill', 0, null, 0],
            ['Outline thick', .025, null, 4],
            ['Outline default', .015, null, 2],
            ['Outline thin', .005, null, 1],
            ['Outline dashed', .005, [.01], 1]
        ],
        Type.ARRAY
    ],

    markupLineStyle: [[.015, null], Type.ARRAY],
    markupLineStyleOptions: [
        [
            // name, stroke width, dash style, option stroke width
            ['Thick', .025, null, 4],
            ['Default', .015, null, 2],
            ['Thin', .005, null, 1],
            ['Dashed', .005, [.01], 1]
        ],
        Type.ARRAY
    ],

    markupLineDecoration: [['arrow-end'], Type.ARRAY],
    markupLineDecorationOptions: [
        [
            // name, value
            ['None', []],
            ['Single arrow', ['arrow-end']],
            ['Double arrow', ['arrow-begin', 'arrow-end']]
        ],
        Type.ARRAY
    ],
    //#endif

    // hooks
    beforeCreateBlob: [null, Type.FUNCTION], // expects promise resolved with canvas
    afterCreateBlob: [null, Type.FUNCTION], // expects promise resolved with blob
    afterCreateOutput: [null, Type.FUNCTION], // expects promise resolved with output

    // callbacks
    onconfirm: [null, Type.FUNCTION],
    oncancel: [null, Type.FUNCTION],
    onclose: [null, Type.FUNCTION],
    onloadstart: [null, Type.FUNCTION],
    onload: [null, Type.FUNCTION],
    onloaderror: [null, Type.FUNCTION],
    oninit: [null, Type.FUNCTION],
    onready: [null, Type.FUNCTION],
    onupdate: [null, Type.FUNCTION],
    ondestroy: [null, Type.FUNCTION],

    // labels
    labelButtonReset: ['Reset', Type.STRING],
    labelButtonCancel: ['Cancel', Type.STRING],
    labelButtonConfirm: ['Done', Type.STRING],
    labelButtonUtilCrop: ['Crop', Type.STRING],
    labelButtonUtilResize: ['Resize', Type.STRING],
    labelButtonUtilFilter: ['Filter', Type.STRING],
    labelButtonUtilColor: ['Colors', Type.STRING],
    labelButtonUtilMarkup: ['Markup', Type.STRING],
    labelStatusMissingWebGL: ['WebGL is required but is disabled on your browser', Type.STRING],
    labelStatusAwaitingImage: ['Waiting for image…', Type.STRING],
    labelStatusLoadImageError: ['Error loading image…', Type.STRING],
    labelStatusLoadingImage: ['Loading image…', Type.STRING],
    labelStatusProcessingImage: ['Processing image…', Type.STRING],

    //#if !_EXCLUDE_COLOR_UTIL
    labelColorBrightness: ['Brightness', Type.STRING],
    labelColorContrast: ['Contrast', Type.STRING],
    labelColorExposure: ['Exposure', Type.STRING],
    labelColorSaturation: ['Saturation', Type.STRING],
    //#endif

    //#if !_EXCLUDE_MARKUP_UTIL
    labelMarkupTypeRectangle: ['Square', Type.STRING],
    labelMarkupTypeEllipse: ['Circle', Type.STRING],
    labelMarkupTypeText: ['Text', Type.STRING],
    labelMarkupTypeLine: ['Arrow', Type.STRING],
    labelMarkupSelectFontSize: ['Size', Type.STRING],
    labelMarkupSelectFontFamily: ['Font', Type.STRING],
    labelMarkupSelectLineDecoration: ['Decoration', Type.STRING],
    labelMarkupSelectLineStyle: ['Style', Type.STRING],
    labelMarkupSelectShapeStyle: ['Style', Type.STRING],
    labelMarkupRemoveShape: ['Remove', Type.STRING],
    labelMarkupToolSelect: ['Select', Type.STRING],
    labelMarkupToolDraw: ['Draw', Type.STRING],
    labelMarkupToolLine: ['Arrow', Type.STRING],
    labelMarkupToolText: ['Text', Type.STRING],
    labelMarkupToolRect: ['Square', Type.STRING],
    labelMarkupToolEllipse: ['Circle', Type.STRING],
    //#endif

    //#if !_EXCLUDE_RESIZE_UTIL
    labelResizeWidth: ['Width', Type.STRING],
    labelResizeHeight: ['Height', Type.STRING],
    labelResizeApplyChanges: ['Apply', Type.STRING],
    //#endif

    // crop labels
    labelCropInstructionZoom: ['Zoom in and out with your scroll wheel or touchpad.', Type.STRING],
    labelButtonCropZoom: ['Zoom', Type.STRING],
    labelButtonCropRotateLeft: ['Rotate left', Type.STRING],
    labelButtonCropRotateRight: ['Rotate right', Type.STRING],
    labelButtonCropRotateCenter: ['Center rotation', Type.STRING],
    labelButtonCropFlipHorizontal: ['Flip horizontal', Type.STRING],
    labelButtonCropFlipVertical: ['Flip vertical', Type.STRING],
    labelButtonCropAspectRatio: ['Aspect ratio', Type.STRING],
    labelButtonCropToggleLimit: ['Crop selection', Type.STRING],
    labelButtonCropToggleLimitEnable: ['Limited to image', Type.STRING],
    labelButtonCropToggleLimitDisable: ['Select outside image', Type.STRING],

    // pointer scope
    pointerEventsPolyfillScope: ['root', Type.STRING], // or document

    // style of crop corners
    styleCropCorner: ['circle', Type.STRING],

    // set a safe area at the bottom of the viewport when fullscreen
    styleFullscreenSafeArea: [isBrowser() ? /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream ? 'bottom' : 'none' : 'none', Type.STRING],

    // set layout modifiers
    styleLayoutMode: [null, Type.STRING]
};

const limit = (value, min = 0, max = 1) => Math.min(max, Math.max(min, value));

// export const getAngle = (c1, c2) => {
//     const dx = c1.x - c2.x;
//     const dy = c1.y - c2.y;
//     return Math.atan2(dy, dx);
// }

const roundFloat = (value, digits = 10) => parseFloat(value.toFixed(digits));

const vectorEqual = (a, b) => roundFloat(a.x) === roundFloat(b.x) && roundFloat(a.y) === roundFloat(b.y);

const roundVector = (v, digits) => ({
    x: roundFloat(v.x, digits),
    y: roundFloat(v.y, digits),
});

const vectorSubtract = (a, b) => createVector(a.x - b.x, a.y - b.y);

const vectorDot = (a, b) => a.x * b.x + a.y * b.y;

const vectorDistanceSquared = (a, b) => vectorDot(vectorSubtract(a, b), vectorSubtract(a, b));

const vectorDistance = (a, b) => Math.sqrt(vectorDistanceSquared(a, b));

const vectorAngleBetween = (a, b) => {
    const r = vectorSubtract(a, b);
    return Math.atan2(r.y, r.x);
};

const vectorLimit = (v, rect) => createVector(
    limit(v.x, rect.x, rect.x + rect.width),
    limit(v.y, rect.y, rect.y + rect.height)
);

const vectorRotate = (v, radians, origin) => {
    const cos = Math.cos(radians);
    const sin = Math.sin(radians);
    const t = createVector(v.x - origin.x, v.y - origin.y);
    return createVector(
        origin.x + (cos * t.x) - (sin * t.y),
        origin.y + (sin * t.x) + (cos * t.y)
    );
};

const createVector = (x = 0, y = 0) => ({ x, y });

const rectEqualsRect = (a, b) => a.x === b.x && a.y === b.y && a.width === b.width && a.height === b.height;

const rectFitsInRect = (child, container) => {
    const childBounds = rectBounds(child);
    const containerBounds = rectBounds(container);
    return (childBounds.left >= containerBounds.left) &&
    (childBounds.top >= containerBounds.top) &&
    (childBounds.bottom <= containerBounds.bottom) &&
    (childBounds.right <= containerBounds.right);
};

const rotateRectCorners = (corners, rotation, origin) => {

    if (rotation === 0) {
        return {
            tl: corners.tl,
            tr: corners.tr,
            br: corners.br,
            bl: corners.bl
        }
    }

    const tl = vectorRotate(corners.tl, rotation, origin);
    const tr = vectorRotate(corners.tr, rotation, origin);
    const br = vectorRotate(corners.br, rotation, origin);
    const bl = vectorRotate(corners.bl, rotation, origin);

    return {
        tl,
        tr,
        br,
        bl
    }
};


const rectRotate = (rect, rotation, origin) => {

    const { tl, tr, br, bl } = rotateRectCorners(rectCorners(rect), rotation, origin);

    const left = Math.min(tl.x, tr.x, br.x, bl.x);
    const top = Math.min(tl.y, tr.y, br.y, bl.y);
    const right = Math.max(tl.x, tr.x, br.x, bl.x);
    const bottom = Math.max(tl.y, tr.y, br.y, bl.y);

    return createRect(
        left,
        top,
        right - left,
        bottom - top
    );
};

const rectScale = (rect, scalar, origin) => createRect(
    scalar * (rect.x - origin.x) + origin.x,
    scalar * (rect.y - origin.y) + origin.y,
    scalar * rect.width,
    scalar * rect.height
);

const rectTranslate = (rect, translation) => createRect(rect.x + translation.x, rect.y + translation.y, rect.width, rect.height);

const TRANSFORM_MAP = {
    'translate': rectTranslate,
    'rotate': rectRotate,
    'scale': rectScale
};

const rectTransform = (rect, transforms, origin) => transforms.reduce((r, t) => {
    const fn = TRANSFORM_MAP[t[0]];
    const params = t[1];
    const result = fn(r, params, origin);
    return result;
}, rect);

const rectClone = (rect) => createRect(
    rect.x, rect.y, 
    rect.width, rect.height
);

const rectBounds = (rect) => ({
    top: rect.y,
    right: rect.x + rect.width,
    bottom: rect.y + rect.height,
    left: rect.x
});


const rectFromBounds = ({top, right, bottom, left}) => ({
    x: left,
    y: top,
    width: right - left,
    height: bottom - top
});

const rectCenter = (rect) => createVector(
    rect.x + (rect.width * .5),
    rect.y + (rect.height * .5)
);

const rectCorners = rect => ({
    tl: { x: rect.x, y: rect.y },
    tr: { x: rect.x + rect.width, y: rect.y },
    br: { x: rect.x + rect.width, y: rect.y + rect.height },
    bl: { x: rect.x, y: rect.y + rect.height },
});

const createRect = (x, y, width, height) => ({ x, y, width, height });

const getNumericAspectRatioFromString = aspectRatio => {
    if (isEmpty(aspectRatio)) {
        return aspectRatio;
    }
    if(/:/.test(aspectRatio)) {
        const parts = aspectRatio.split(':');
        const w = parts[0];
        const h = parts[1];
        return h / w;
    }
    return parseFloat(aspectRatio);
};

const getCenteredCropRect = (container, aspectRatio) => {

    let width = container.width;
    let height = width * aspectRatio;
    if (height > container.height) {
        height = container.height;
        width = height / aspectRatio;
    }
    const x = ((container.width - width) * .5);
    const y = ((container.height - height) * .5);

    return {
        x, y, width, height
    }
};

const calculateCanvasSize = (image, canvasAspectRatio, zoom = 1) => {

    const imageAspectRatio = image.height / image.width;

    // determine actual pixels on x and y axis
    let canvasWidth = 1;
    let canvasHeight = canvasAspectRatio;
    let imgWidth = 1;
    let imgHeight = imageAspectRatio;
    if (imgHeight > canvasHeight) {
        imgHeight = canvasHeight;
        imgWidth = imgHeight / imageAspectRatio;
    }

    const scalar = Math.max(canvasWidth / imgWidth, canvasHeight / imgHeight);
    const width = image.width / (zoom * scalar * imgWidth);
    const height = width * canvasAspectRatio;

    return {
        width: width,
        height: height
    }
};

const createVector$1 = (x,y) => ({x,y});

const vectorDot$1 = (a, b) => a.x * b.x + a.y * b.y;

const vectorSubtract$1 = (a, b) => createVector$1(a.x - b.x, a.y - b.y);

const vectorDistanceSquared$1 = (a, b) => vectorDot$1(vectorSubtract$1(a, b), vectorSubtract$1(a, b));

const vectorDistance$1 = (a, b) => Math.sqrt(vectorDistanceSquared$1(a, b));

const getOffsetPointOnEdge = (length, rotation) => {

    const a = length;

    const A = 1.5707963267948966;
    const B = rotation;
    const C = 1.5707963267948966 - rotation;

    const sinA = Math.sin(A);
    const sinB = Math.sin(B);
    const sinC = Math.sin(C);
    const cosC = Math.cos(C);
    const ratio = a / sinA;
    const b = ratio * sinB;
    const c = ratio * sinC;

    return createVector$1(cosC * b, cosC * c);
};

const getRotatedRectSize = (rect, rotation) => {

    const w = rect.width;
    const h = rect.height;

    const hor = getOffsetPointOnEdge(w, rotation);
    const ver = getOffsetPointOnEdge(h, rotation);

    const tl = createVector$1(
        rect.x + Math.abs(hor.x),
        rect.y - Math.abs(hor.y)
    );

    const tr = createVector$1(
        rect.x + rect.width + Math.abs(ver.y),
        rect.y + Math.abs(ver.x)
    );

    const bl = createVector$1(
        rect.x - Math.abs(ver.y),
        (rect.y + rect.height) - Math.abs(ver.x)
    );
    
    return {
        width: vectorDistance$1(tl, tr),
        height: vectorDistance$1(tl, bl)
    }

};


const getImageRectZoomFactor = (imageRect, cropRect, rotation = 0, center = { x:.5, y:.5 }) => {

    // calculate available space round image center position
    const cx = center.x > .5 ? 1 - center.x : center.x;
    const cy = center.y > .5 ? 1 - center.y : center.y;
    const imageWidth = cx * 2 * imageRect.width;
    const imageHeight = cy * 2 * imageRect.height;

    // calculate rotated crop rectangle size
    const rotatedCropSize = getRotatedRectSize(cropRect, rotation);

    return Math.max(
        rotatedCropSize.width / imageWidth, 
        rotatedCropSize.height / imageHeight
    );
};

const getAxisAlignedImageRect = (image, { origin, translation, scale }) => rectTransform(image, [
    ['scale', scale],
    ['translate', translation],
],
origin);

const getOffsetPointOnEdge$1 = (length, rotation) => {

    const a = length;

    const A = 1.5707963267948966;
    const B = rotation;
    const C = 1.5707963267948966 - rotation;

    const sinA = Math.sin(A);
    const sinB = Math.sin(B);
    const sinC = Math.sin(C);
    const cosC = Math.cos(C);
    const ratio = a / sinA;
    const b = ratio * sinB;
    const c = ratio * sinC;

    return createVector(cosC * b, cosC * c);
};

const getRotatedRectCorners = (rect, rotation) => {

    const w = rect.width;
    const h = rect.height;

    const r = rotation % (Math.PI / 2);

    const hor = getOffsetPointOnEdge$1(w, r);
    const ver = getOffsetPointOnEdge$1(h, r);

    let tl, tr, br, bl;

    const corners = rectCorners(rect);

    tl = createVector(
        corners.tl.x + Math.abs(hor.x),
        corners.tl.y - Math.abs(hor.y)
    );

    tr = createVector(
        corners.tr.x + Math.abs(ver.y),
        corners.tr.y + Math.abs(ver.x)
    );

    br = createVector(
        corners.br.x - Math.abs(hor.x),
        corners.br.y + Math.abs(hor.y)
    );

    bl = createVector(
        corners.bl.x - Math.abs(ver.y),
        corners.bl.y - Math.abs(ver.x)
    );

    return {
        tl,
        tr,
        br,
        bl
    }

};

const getAxisAlignedCropRect = (origin, translation, rotation, rect) => {

    const rotatedImageRectPivot = {
        x: origin.x + translation.x,
        y: origin.y + translation.y
    };
    
    // calculate axis aligned bounding box of crop rectangle
    const cropRotatedCorners = getRotatedRectCorners(rect, rotation);
    const tl = vectorRotate(cropRotatedCorners.tl, -rotation, rotatedImageRectPivot);
    const tr = vectorRotate(cropRotatedCorners.tr, -rotation, rotatedImageRectPivot);
    const br = vectorRotate(cropRotatedCorners.br, -rotation, rotatedImageRectPivot);
    
    return createRect(
        Math.min(tl.x, tr.x, br.x), // x
        Math.min(tl.y, tr.y, br.y), // y
        Math.max(tl.x, tr.x, br.x) - Math.min(tl.x, tr.x, br.x), // width
        Math.max(tl.y, tr.y, br.y) - Math.min(tl.y, tr.y, br.y) // height
    );
};

const getCropFromView = (image, rect, imageTransforms, limitToImageBounds = true) => {

    const { origin, translation } = imageTransforms;

    // calculate axis aligned bounding box of image
    const axisAlignedImageRect = getAxisAlignedImageRect(image, imageTransforms);

    // get rotation
    const rotation = (Math.PI * 2) + (imageTransforms.rotation % (Math.PI * 2));

    const axisAlignedCropRect = getAxisAlignedCropRect(origin, translation, rotation, rect);

    const axisAlignedCropRectCenter = rectCenter(axisAlignedCropRect);

    // get current crop aspect ratio
    const aspectRatio = rect.height / rect.width;

    // calculate center of output ( is center of crop rect relative to image position )
    const center = {
        x: (axisAlignedCropRectCenter.x - axisAlignedImageRect.x) / axisAlignedImageRect.width,
        y: (axisAlignedCropRectCenter.y - axisAlignedImageRect.y) / axisAlignedImageRect.height
    };

    // calculate available space round image center position
    const cx = center.x > .5 ? 1 - center.x : center.x;
    const cy = center.y > .5 ? 1 - center.y : center.y;
    const imageWidth = cx * 2 * axisAlignedImageRect.width;
    const imageHeight = cy * 2 * axisAlignedImageRect.height;

    let zoom;
    if (limitToImageBounds) {
        zoom = Math.min(
            imageWidth / axisAlignedCropRect.width,
            imageHeight / axisAlignedCropRect.height
        );
    }
    else {
        zoom = Math.min(
            axisAlignedImageRect.width / axisAlignedCropRect.width,
            axisAlignedImageRect.height / axisAlignedCropRect.height
        );
    }

    return {
        center,
        zoom,
        rotation: imageTransforms.rotation,
        aspectRatio,
        scaleToFit: limitToImageBounds
    };

};

const getCropFromStateRounded = (image, crop) => {
    const rawCrop = getCropFromState(image, crop);
    return {
        center: {
            x: roundFloat(rawCrop.center.x),
            y: roundFloat(rawCrop.center.y)
        },
        rotation: roundFloat(rawCrop.rotation),
        zoom: roundFloat(rawCrop.zoom),
        aspectRatio: roundFloat(rawCrop.aspectRatio),
        flip: {
            ...crop.flip
        },
        scaleToFit: rawCrop.scaleToFit
    };
};

const getCropFromState = (image, crop) => {

    // get the raw output crop (has crazy floats)
    const rawCrop = getCropFromView(
        image, 
        crop.rectangle, 
        crop.transforms,
        crop.limitToImageBounds
    );

    // create the new crop outline
    return {
        center: {
            x: rawCrop.center.x,
            y: rawCrop.center.y
        },
        rotation: rawCrop.rotation,
        zoom: rawCrop.zoom,
        aspectRatio: rawCrop.aspectRatio,
        flip: {
            ...crop.flip
        },
        scaleToFit: rawCrop.scaleToFit
    };
};

const limitSize = (size, min, max, aspectRatio, anchor = 'width') => {

    let { width, height } = size;

    // no forced size
    if (!width && !height) return { width, height };
    
    // limit supplied values first
    width = width && limit(width, min.width, max.width);
    height = height && limit(height, min.height, max.height);

    // no aspect ratio enforced
    if (!aspectRatio) {
        return { width, height };
    }
    
    // no height supplied, make sure auto-height fits size contraints
    if (!height) {
        const heightAuto = limit(width / aspectRatio, min.height, max.height);
        width = heightAuto * aspectRatio;
    }

    // no width supplied, make sure auto-width fits size contraints
    else if (!width) {
        const widthAuto = limit(height * aspectRatio, min.width, max.width);
        height = widthAuto / aspectRatio;
    }

    // both width and height have been defined, make sure conforms to aspect ratio
    else {

        if (anchor === 'width') {
            height = width / aspectRatio;
        }
        else if (anchor === 'height') {
            width = height * aspectRatio;
        }
        else {
            if (height * aspectRatio < min.width) {
                width = min.width;
                height = width / aspectRatio;
            }
            else if (width / aspectRatio < min.height) {
                height = min.height;
                width = height * aspectRatio;
            }

            if (height * aspectRatio > max.width) {
                width = max.width;
                height = width / aspectRatio;
            }
            else if (width / aspectRatio > max.height) {
                height = max.height;
                width = height * aspectRatio;
            }
        }
    }

    return {
        width,
        height
    }
};

const dotColorMatrix = (a, b) => {
    const res = new Array(20);

    // R
    res[0] = (a[0] * b[0]) + (a[1] * b[5]) + (a[2] * b[10]) + (a[3] * b[15]);
    res[1] = (a[0] * b[1]) + (a[1] * b[6]) + (a[2] * b[11]) + (a[3] * b[16]);
    res[2] = (a[0] * b[2]) + (a[1] * b[7]) + (a[2] * b[12]) + (a[3] * b[17]);
    res[3] = (a[0] * b[3]) + (a[1] * b[8]) + (a[2] * b[13]) + (a[3] * b[18]);
    res[4] = (a[0] * b[4]) + (a[1] * b[9]) + (a[2] * b[14]) + (a[3] * b[19]) + a[4];

    // G
    res[5] = (a[5] * b[0]) + (a[6] * b[5]) + (a[7] * b[10]) + (a[8] * b[15]);
    res[6] = (a[5] * b[1]) + (a[6] * b[6]) + (a[7] * b[11]) + (a[8] * b[16]);
    res[7] = (a[5] * b[2]) + (a[6] * b[7]) + (a[7] * b[12]) + (a[8] * b[17]);
    res[8] = (a[5] * b[3]) + (a[6] * b[8]) + (a[7] * b[13]) + (a[8] * b[18]);
    res[9] = (a[5] * b[4]) + (a[6] * b[9]) + (a[7] * b[14]) + (a[8] * b[19]) + a[9];

    // B
    res[10] = (a[10] * b[0]) + (a[11] * b[5]) + (a[12] * b[10]) + (a[13] * b[15]);
    res[11] = (a[10] * b[1]) + (a[11] * b[6]) + (a[12] * b[11]) + (a[13] * b[16]);
    res[12] = (a[10] * b[2]) + (a[11] * b[7]) + (a[12] * b[12]) + (a[13] * b[17]);
    res[13] = (a[10] * b[3]) + (a[11] * b[8]) + (a[12] * b[13]) + (a[13] * b[18]);
    res[14] = (a[10] * b[4]) + (a[11] * b[9]) + (a[12] * b[14]) + (a[13] * b[19]) + a[14];

    // A
    res[15] = (a[15] * b[0]) + (a[16] * b[5]) + (a[17] * b[10]) + (a[18] * b[15]);
    res[16] = (a[15] * b[1]) + (a[16] * b[6]) + (a[17] * b[11]) + (a[18] * b[16]);
    res[17] = (a[15] * b[2]) + (a[16] * b[7]) + (a[17] * b[12]) + (a[18] * b[17]);
    res[18] = (a[15] * b[3]) + (a[16] * b[8]) + (a[17] * b[13]) + (a[18] * b[18]);
    res[19] = (a[15] * b[4]) + (a[16] * b[9]) + (a[17] * b[14]) + (a[18] * b[19]) + a[19];

    return res;
};

const toRGBColorArray = (color) => {
    let colors;
    if (/^#/.test(color)) {
        if (color.length === 4) {
            var parts = color.split('');
            color = '#' + parts[1] + parts[1] + parts[2] + parts[2] + parts[3] + parts[3];
        }
        // hex
        const matches = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(color);
        colors = [
            parseInt(matches[1], 16),
            parseInt(matches[2], 16),
            parseInt(matches[3], 16)
        ];
    }
    else if (/^rgb/.test(color)) {
        const matches = color.match(/\d+/g);
        colors = matches.map(v => parseInt(v, 10));
        colors.length = 3;
    }
    return colors;
};

let viewCache = [];

const getColorMatrixFromMatrices = (matrices) => {
    const list = [];
    forin(matrices, (key, value) => list.push(value));
    return list.length ? list.reduce((previous, current) => dotColorMatrix([...previous], current), list.shift()) : [];
};

const getImageScalar = state => state.crop.draft.transforms ? state.crop.draft.transforms.scale : state.crop.transforms.scale;

const getMinCropSize = state => {
    const imagePreviewScaleFactor = state.image.width / state.image.naturalWidth;
    const imageScalar = getImageScalar(state);
    return {
        width: state.options.cropMinImageWidth * imageScalar * imagePreviewScaleFactor,
        height: state.options.cropMinImageHeight * imageScalar * imagePreviewScaleFactor
    };
};

const getMaxCropSize = state => {
    const imageScalar = getImageScalar(state);
    return {
        width: state.image.width * imageScalar,
        height: state.image.height * imageScalar
    };
};

const getWidth = (state) => state.options.size ? state.options.size.width : null;
const getHeight = (state) => state.options.size ? state.options.size.height : null;
const getOutputSizeWidth = (state) => state.size.width === false ? getWidth(state) : state.size.width;
const getOutputSizeHeight = (state) => state.size.height === false ? getHeight(state) : state.size.height;

const getAspectRatioOptions = state => state.options.cropAspectRatioOptions ? state.options.cropAspectRatioOptions.map(option => {

    let value = {
        aspectRatio: null,
        width: null,
        height: null
    };

    if (typeof option.value === 'number') {
        value.aspectRatio = option.value;
    }

    if (typeof option.value === 'string') {
        value.aspectRatio = getNumericAspectRatioFromString(option.value);
    }

    if (typeof option.value === 'object' && option.value !== null) {
        value.width = option.value.width;
        value.height = option.value.height;
        value.aspectRatio = value.height / value.width;
    }

    return {
        label: option.label,
        value
    }
    
}) : null;

const getCropStageRect = (state, crop) => {

    // aspect ratio for this calculation is reversed, it's how it is, will be fixed in the future
    if (!crop.aspectRatio) crop.aspectRatio = state.image.height / state.image.width;
    const size = getCurrentCropSize(state.image, crop, crop.scaleToFit);

    let aspectRatio = size.width / size.height;
    if (state.stage.width < size.width) {
        size.width = state.stage.width;
        size.height = size.width / aspectRatio;
    }
    if (state.stage.height < size.height) {
        size.height = state.stage.height;
        size.width = size.height * aspectRatio;
    }

    return createRect(
        (state.stage.width * .5) - (size.width * .5),
        (state.stage.height * .5) - (size.height * .5),
        size.width,
        size.height
    );
};

const getImageStageRect = state => {
    
    let imageWidth = state.image.naturalWidth;
    let imageHeight = state.image.naturalHeight;
    let aspectRatio = imageHeight / imageWidth;

    if (state.stage.width < imageWidth) {
        imageWidth = state.stage.width;
        imageHeight = aspectRatio * imageWidth;
    }
    if (state.stage.height < imageHeight) {
        imageHeight = state.stage.height;
        imageWidth = imageHeight / aspectRatio;
    }

    return createRect(
        (state.stage.width * .5) - (imageWidth * .5),
        (state.stage.height * .5) - (imageHeight * .5),
        imageWidth,
        imageHeight
    );

};

const queries = state => ({

    GET_SIZE: () => {
        if (state.size.width !== false && state.size.height !== false) {
            return {
                width: state.size.width,
                height: state.size.height
            }
        }
        return {
            width: null,
            height: null
        }
    },
    GET_SIZE_INPUT: () => ({ width: state.size.width, height: state.size.height }),
    GET_SIZE_ASPECT_RATIO_LOCK: () => state.size.aspectRatioLocked,

    IS_ACTIVE_VIEW: (viewId) => state.activeView === viewId,

    GET_ACTIVE_VIEW: () => state.activeView,

    GET_STYLES: () => Object.keys(state.options)
        .filter(key => /^style/.test(key))
        .map(option => ({
            name: option,
            value: state.options[option]
        })),
    
    GET_FILE:() => state.file,

    GET_IMAGE:() => state.image,

    GET_STAGE: () => ({
        ...state.stage,
        ...state.stageOffset
    }),

    GET_STAGE_RECT: (crop) => {

        let rect;
        const mode = state.options.imagePreviewScaleMode;
        
        if (mode === 'crop') {
            rect = crop ? getCropStageRect(state, crop) : getImageStageRect(state);
        }
        else if (mode === 'image') {
            rect = getImageStageRect(state);
        }
        else {
            rect = {...state.stage};
        }
    
        rect.fits = rect.width < state.stage.width && rect.height < state.stage.height;
        rect.mode = mode;

        return rect;
    },

    GET_IMAGE_STAGE_RECT: () => getImageStageRect(state),

    GET_ROOT: () => state.rootRect,

    GET_ROOT_SIZE: () => ({
        width: state.rootRect.width,
        height: state.rootRect.height
    }),

    GET_MIN_IMAGE_SIZE: () => ({
        width: state.options.cropMinImageWidth,
        height: state.options.cropMinImageHeight
    }),

    GET_IMAGE_PREVIEW_SCALE_FACTOR: () => state.image.width / state.image.naturalWidth,

    GET_MIN_PREVIEW_IMAGE_SIZE: () => {
        const imagePreviewScaleFactor = state.image.width / state.image.naturalWidth;
        return {
            width: state.options.cropMinImageWidth * imagePreviewScaleFactor,
            height: state.options.cropMinImageHeight * imagePreviewScaleFactor
        }
    },

    GET_MIN_CROP_SIZE: () => getMinCropSize(state),

    GET_MAX_CROP_SIZE: () => getMaxCropSize(state),

    GET_MIN_PIXEL_CROP_SIZE: () => {
        const scale = state.crop.transforms.scale;
        const size = getMinCropSize(state);
        return {
            width: size.width / scale,
            height: size.height / scale,
        }
    },

    GET_MAX_PIXEL_CROP_SIZE: () => {
        const scale = state.crop.transforms.scale;
        const size = getMaxCropSize(state);
        return {
            width: size.width / scale,
            height: size.height / scale,
        }
    },

    GET_CROP_ASPECT_RATIO_OPTIONS: () => getAspectRatioOptions(state),

    GET_ACTIVE_CROP_ASPECT_RATIO: () => {
        const aspectRatio = state.crop.aspectRatio;
        return isString(aspectRatio) ? getNumericAspectRatioFromString(aspectRatio) : aspectRatio; 
    },

    GET_CROP_ASPECT_RATIO: () => {

        const aspectRatio = isString(state.options.cropAspectRatio) ? getNumericAspectRatioFromString(state.options.cropAspectRatio) : state.options.cropAspectRatio;
        const aspectRatioOptions = getAspectRatioOptions(state);

        // no options, use the set aspect ratio
        if (!aspectRatioOptions || (aspectRatioOptions && !aspectRatioOptions.length)) {
            return aspectRatio;
        }

        // test if matches an aspect ratio option, if not, fallback
        const aspectRatioOption = aspectRatioOptions.find(option => option.value.aspectRatio === aspectRatio);
        if (!aspectRatioOption) {
            return aspectRatioOptions[0].value.aspectRatio;
        }

        // return default aspect ratio
        return aspectRatio;
    },

    GET_CROP_RECTANGLE_ASPECT_RATIO: () => {
        const { rectangle, aspectRatio } = state.crop;
        if (rectangle) return rectangle.width / rectangle.height;
        return aspectRatio;
    },

    GET_CROP:(id, ts) => {

        const cached = viewCache[id];
        if (cached && cached.ts === ts) {
            return cached;
        }

        const view = getCropView(state);
        if (view) {
            view.ts = ts;
            viewCache[id] = view;
        }
        
        return view;
    },

    GET_COLOR_MATRIX: () => getColorMatrixFromMatrices(state.colorMatrices),

    GET_COLOR_VALUES: () => ({
        exposure: state.options.colorExposure,
        brightness: state.options.colorBrightness,
        contrast: state.options.colorContrast,
        saturation: state.options.colorSaturation,
        ...state.colorValues
    }),

    GET_MARKUP_TOOL_VALUES: () => ({
        color: state.options.markupColor,
        fontFamily: state.options.markupFontFamily,
        fontSize: state.options.markupFontSize,
        shapeStyle: state.options.markupShapeStyle,
        lineStyle: state.options.markupLineStyle,
        lineDecoration: state.options.markupLineDecoration,
        ...state.markupToolValues
    }),

    GET_PREVIEW_IMAGE_DATA: () => state.file.preview,

    GET_THUMB_IMAGE_DATA: () => state.file.thumb,

    GET_FILTER: () => state.filter,

    GET_UID: () => state.uid,

    GET_MARKUP_BY_ID: (id) => state.markup.find(markup => markup[1].id === id),

    GET_BACKGROUND_COLOR: () => {

        // get color
        const color = state.options.outputCanvasBackgroundColor;

        // no color found
        if (!color) return COLOR_TRANSPARENT;

        // should return color, check if has been calculated earlier
        else if (ColorTable[color]) return ColorTable[color];
        
        // values not calculated, let's calculate and store
        const colorArray = toRGBColorArray(color);
        ColorTable[color] = colorArray.concat(1);
        return ColorTable[color];
    }
});

const ColorTable = {};
const COLOR_TRANSPARENT = [0, 0, 0, 0];

const getCurrentImageSize = (state, crop) => {
    
    // get current image output width and height
    let width = getOutputSizeWidth(state);
    let height = getOutputSizeHeight(state);

    // calculate crop rectangle aspect ratio
    const cropAspectRatio = crop.width / crop.height;

    // calculate limited size
    return limitSize({ width, height }, state.options.sizeMin, state.options.sizeMax, cropAspectRatio);
};

const getCurrentCropSize = (imageSize, crop = {}, limitToImageBounds = true) => {
    
    let { zoom, rotation, center, aspectRatio } = crop;

    const canvasSize = calculateCanvasSize(imageSize, aspectRatio, zoom);
    
    const canvasCenter = {
        x: canvasSize.width * .5,
        y: canvasSize.height * .5
    };

    const stage = {
        x: 0,
        y: 0,
        width: canvasSize.width,
        height: canvasSize.height,
        center: canvasCenter
    };
    
    const scale = zoom * getImageRectZoomFactor(
        imageSize,
        getCenteredCropRect(stage, aspectRatio),
        rotation,
        limitToImageBounds ? center : {
            x: .5,
            y: .5
        }
    );

    // start drawing
    return {
        widthFloat: canvasSize.width / scale,
        heightFloat: canvasSize.height / scale,
        width: Math.round(canvasSize.width / scale),
        height: Math.round(canvasSize.height / scale)
    }
};

const canZoom = (rectangle, stageRect) => {
    const stageCenter = rectCenter(stageRect);
    const rectangleCenter = rectCenter(rectangle);
    return !vectorEqual(rectangleCenter, stageCenter);
};

// triggers and update of the crop and image
const getCropView = (state) => {

    // if we don't have image or stage data we can't update the view
    if (!state.stage || !state.image) return null;

    // get current crop rectangle
    const rectangle = state.crop.draft.rectangle || { free: state.crop.rectangle, limited: state.crop.rectangle };

    // calculate image rect
    const { origin, translation, scale, interaction } = state.crop.draft.transforms || state.crop.transforms;

    // rotation situation
    const rotation = state.crop.rotation;

    // flip state
    const flip = state.crop.flip;

    // are currently drafting
    const drafting = (!!(state.crop.draft.rectangle || state.crop.draft.transforms));
    const isDraft = drafting || state.instantUpdate;

    // determine if rectangle is centered in the viewport
    const canRecenter = canZoom(rectangle.limited, state.stage);

    // always reset
    const canReset = state.crop.isDirty || drafting;

    // is currently rotating
    const isRotating = state.crop.isRotating;

    // is limited
    const isLimitedToImageBounds = 
        typeof state.crop.limitToImageBounds === 'undefined'
         ? true 
         : state.crop.limitToImageBounds;

    // calculate current image output size
    const imageSize = {
        width: state.image.naturalWidth,
        height: state.image.naturalHeight
    };

    const colorMatrix = getColorMatrixFromMatrices(state.colorMatrices);

    const cropDescription = getCropFromState(
        state.image, 
        {
            rectangle: rectangle.limited,
            transforms: { 
                origin, 
                translation, 
                scale, 
                rotation: rotation.main + rotation.sub 
            },
            flip,
            limitToImageBounds: state.crop.limitToImageBounds
        }
    );

    const cropStatus = {
        props: cropDescription,
        crop: getCurrentCropSize(
            imageSize, 
            cropDescription,
            state.crop.limitToImageBounds
        ),
        image: getCurrentImageSize(
            state, 
            rectangle.limited
        )
    };

    // share current crop size
    {
        const { image, crop } = cropStatus;

        let currentWidth = crop.width;
        let currentHeight = crop.height;

        let cropRectangleAspectRatio = crop.widthFloat / crop.heightFloat;
    
        if (image.width && image.height) {
            currentWidth = image.width;
            currentHeight = image.height;
        }
        else if (image.width && !image.height) {
            currentWidth = image.width;
            currentHeight = image.width / cropRectangleAspectRatio;
        }
        else if (image.height && !image.width) {
            currentHeight = image.height;
            currentWidth = image.height * cropRectangleAspectRatio;
        }
        
        cropStatus.currentWidth = Math.round(currentWidth);
        cropStatus.currentHeight = Math.round(currentHeight);
    }

    let translationBand = {x:0, y:0};
    let scaleBand = 0;
    let rotationBand = 0;

    if (isDraft && interaction) {

        if (interaction.translation) {

            const DIST = 100;
            const x = interaction.translation.x - translation.x;
            const y = interaction.translation.y - translation.y;

            translationBand.x = DIST * Math.sign(x) * Math.log10(1 + (Math.abs(x) / DIST));
            translationBand.y = DIST * Math.sign(y) * Math.log10(1 + (Math.abs(y) / DIST));            
        }

        if (interaction.scale) {
            const DIST = .25;
            const s = interaction.scale - scale;
            scaleBand = DIST * Math.sign(s) * Math.log10(1 + (Math.abs(s) / DIST));
        }

        if (interaction.rotation) {
            const DIST = .05;
            const s = interaction.rotation - (rotation.main + rotation.sub);
            rotationBand = DIST * Math.sign(s) * Math.log10(1 + (Math.abs(s) / DIST));
        }
    }

    const bounds = {};
    const interactionRect = rectangle.free;
    const interactionBounds = rectBounds(interactionRect);
    const limitedBounds = rectBounds(rectangle.limited);
    
    forin(interactionBounds, (key) => {
        const DIST = 5;
        const s = interactionBounds[key] - limitedBounds[key];
        bounds[key] = limitedBounds[key] + (DIST * Math.sign(s) * Math.log10(1 + (Math.abs(s) / DIST)));
    });

    const cropRect = {
        x: bounds.left,
        y: bounds.top,
        width: bounds.right - bounds.left,
        height: bounds.bottom - bounds.top
    };

    const markup = state.markup;

    const previewSize = {
        width: state.image.width,
        height: state.image.height
    };

    // new view
    return {

        // button states
        canRecenter,
        canReset,

        // data states
        isDraft,
        isRotating,
        isLimitedToImageBounds,

        // crop rectangle
        cropRect,
        
        // image transforms
        origin,
        translation,
        translationBand,
        scale,
        scaleBand,
        rotation,
        rotationBand,
        flip,

        // intended interaction, used to calculate elasticity
        interaction,

        // current image output size should this crop be made
        cropStatus,
        
        // current color matrix to apply
        colorMatrix,

        // markup
        markup,

        // info
        previewSize
    };

};

// test if file is of type image
const isImage = file => /^image/.test(file.type);

const MATRICES = {
    1: () => [1, 0, 0, 1, 0, 0],
    2: width => [-1, 0, 0, 1, width, 0],
    3: (width, height) => [-1, 0, 0, -1, width, height],
    4: (width, height) => [1, 0, 0, -1, 0, height],
    5: () => [0, 1, 1, 0, 0, 0],
    6: (width, height) => [0, 1, -1, 0, height, 0],
    7: (width, height) => [0, -1, -1, 0, height, width],
    8: width => [0, -1, 1, 0, 0, width]
};

const getImageOrientationMatrix = (width, height, orientation) => {
    if (orientation === -1) orientation = 1;
    return MATRICES[orientation](width, height);
};

const canvasRelease = canvas => {
    canvas.width = 1;
    canvas.height = 1;
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, 1, 1);
};

const isFlipped = (flip) => flip && (flip.horizontal || flip.vertical);

const getBitmap = (image, orientation, flip) => {

    if (orientation <= 1 && !isFlipped(flip)) {
        image.width = image.naturalWidth;
        image.height = image.naturalHeight;
        return image;
    }
    
    const canvas = document.createElement('canvas');
    const width = image.naturalWidth;
    const height = image.naturalHeight;
  
    // if is rotated incorrectly swap width and height
    const swapped = orientation >= 5 && orientation <= 8;
    if (swapped) {
      canvas.width = height;
      canvas.height = width;
    }
    else {
      canvas.width = width;
      canvas.height = height;
    }

    // draw the image but first fix orientation and set correct flip
    const ctx = canvas.getContext('2d');

    // get base transformation matrix
    if (orientation) {
        ctx.transform.apply(ctx, getImageOrientationMatrix(width, height, orientation));
    }

    if (isFlipped(flip)) {

        // flip horizontal
        // [-1, 0, 0, 1, width, 0]
        const matrix = [1, 0, 0, 1, 0, 0];
        if ((!swapped && flip.horizontal) || (swapped & flip.vertical)) {
            matrix[0] = -1;
            matrix[4] = width;
        }

        // flip vertical
        // [1, 0, 0, -1, 0, height]
        if ((!swapped && flip.vertical) || (swapped && flip.horizontal)) {
            matrix[3] = -1;
            matrix[5] = height;
        }

        ctx.transform(...matrix);
    }

    ctx.drawImage(image, 0, 0, width, height);
    
    return canvas;
  
};

const imageToImageData = (imageElement, orientation, crop = {}, options = {}) => {
    
    const { canvasMemoryLimit, background = null } = options;

    const zoom = crop.zoom || 1;

    // fixes possible image orientation problems by drawing the image on the correct canvas
    const bitmap = getBitmap(imageElement, orientation, crop.flip);
    const imageSize = {
        width: bitmap.width,
        height: bitmap.height
    };

    
    const aspectRatio = crop.aspectRatio || imageSize.height / imageSize.width;

    let canvasSize = calculateCanvasSize(imageSize, aspectRatio, zoom);
    
    if (canvasMemoryLimit) {
        const requiredMemory = canvasSize.width * canvasSize.height;
        if (requiredMemory > canvasMemoryLimit) {
            const scalar = Math.sqrt(canvasMemoryLimit) / Math.sqrt(requiredMemory);
            imageSize.width = Math.floor(imageSize.width * scalar);
            imageSize.height = Math.floor(imageSize.height * scalar);
            canvasSize = calculateCanvasSize(imageSize, aspectRatio, zoom);
        }
    }

    const canvas = document.createElement('canvas');
    
    const canvasCenter = {
        x: canvasSize.width * .5,
        y: canvasSize.height * .5
    };

    const stage = {
        x: 0,
        y: 0,
        width: canvasSize.width,
        height: canvasSize.height,
        center: canvasCenter
    };

    const shouldLimit = typeof crop.scaleToFit === 'undefined' || crop.scaleToFit;

    const scale = zoom * getImageRectZoomFactor(
        imageSize,
        getCenteredCropRect(stage, aspectRatio),
        crop.rotation,
        shouldLimit ? crop.center : { x:.5, y:.5 }
    );

    // start drawing
    canvas.width = Math.round(canvasSize.width / scale);
    canvas.height = Math.round(canvasSize.height / scale);

    canvasCenter.x /= scale;
    canvasCenter.y /= scale;

    const imageOffset = {
        x: canvasCenter.x - (imageSize.width * (crop.center ? crop.center.x : .5)),
        y: canvasCenter.y - (imageSize.height * (crop.center ? crop.center.y : .5))
    };
    
    const ctx = canvas.getContext('2d');
    if (background) {
        ctx.fillStyle = background;
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }

    // move to draw offset
    ctx.translate(canvasCenter.x, canvasCenter.y);
    ctx.rotate(crop.rotation || 0);

    // draw the image
    ctx.drawImage(
        bitmap, 
        imageOffset.x - canvasCenter.x, 
        imageOffset.y - canvasCenter.y, 
        imageSize.width, 
        imageSize.height
    );

    // get data from canvas
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    
    // release canvas
    canvasRelease(canvas);

    // return data
    return imageData;
};

/**
 * Polyfill toBlob for Edge
 */
const IS_BROWSER$1 = (() => typeof window !== 'undefined' && typeof window.document !== 'undefined')();
if (IS_BROWSER$1) {
    if (!HTMLCanvasElement.prototype.toBlob) {
        Object.defineProperty(HTMLCanvasElement.prototype, 'toBlob', {
            value: function (callback, type, quality) {
                var dataURL = this.toDataURL(type, quality).split(',')[1];
                setTimeout(function() {
                    var binStr = atob(dataURL);
                    var len = binStr.length;
                    var arr = new Uint8Array(len);
                    for (var i=0; i<len; i++) {
                        arr[i] = binStr.charCodeAt(i);
                    }
                    callback(new Blob([arr], {type: type || 'image/png'}));
                });
            }
        });
    }
}

const canvasToBlob = (canvas, options, beforeCreateBlob = null) =>
    new Promise((resolve) => {
        const promisedImage = beforeCreateBlob ? beforeCreateBlob(canvas) : canvas;
        Promise.resolve(promisedImage).then(canvas => {
            canvas.toBlob(resolve, options.type, options.quality);
        });
    });

const vectorMultiply$1 = (v, amount) => createVector$2(v.x * amount, v.y * amount);

const vectorAdd$1 = (a, b) => createVector$2(a.x + b.x, a.y + b.y);

const vectorNormalize$1 = (v) => {
    const l = Math.sqrt(v.x * v.x + v.y * v.y);
    if (l === 0) {
        return {
            x: 0,
            y: 0
        }
    }
    return createVector$2(v.x / l, v.y / l);
};

const vectorRotate$1 = (v, radians, origin) => {
    const cos = Math.cos(radians);
    const sin = Math.sin(radians);
    const t = createVector$2(v.x - origin.x, v.y - origin.y);
    return createVector$2(
        origin.x + (cos * t.x) - (sin * t.y),
        origin.y + (sin * t.x) + (cos * t.y)
    );
};

const createVector$2 = (x = 0, y = 0) => ({ x, y });

const getMarkupValue = (value, size, scalar = 1, axis) => {
    if (typeof value === 'string') {
        return parseFloat(value) * scalar;
    }
    if (typeof value === 'number') {
        return value * (axis ? size[axis] : Math.min(size.width, size.height));
    }
    return;
};

//#if !_EXCLUDE_MARKUP_UTIL
const getMarkupStyles = (markup, size, scale) => {
    const lineStyle = markup.borderStyle || markup.lineStyle || 'solid';
    const fill = markup.backgroundColor || markup.fontColor || 'transparent';
    const stroke =  markup.borderColor || markup.lineColor || 'transparent';
    const strokeWidth = getMarkupValue(markup.borderWidth || markup.lineWidth, size, scale);
    const lineCap = markup.lineCap || 'round';
    const lineJoin = markup.lineJoin || 'round';
    const dashes = typeof lineStyle === 'string' ? '' : lineStyle.map(v => getMarkupValue(v, size, scale)).join(',');
    const opacity = markup.opacity || 1;
    return {
        'stroke-linecap': lineCap,
        'stroke-linejoin': lineJoin,
        'stroke-width': strokeWidth || 0,
        'stroke-dasharray': dashes,
        stroke,
        fill,
        opacity
    }
};
//#endif

//#if !_EXCLUDE_MARKUP_UTIL
const isDefined = value => value != null;

const getMarkupRect = (rect, size, scalar = 1) => {

    let left = getMarkupValue(rect.x, size, scalar, 'width') || getMarkupValue(rect.left, size, scalar, 'width');
    let top = getMarkupValue(rect.y, size, scalar, 'height') || getMarkupValue(rect.top, size, scalar, 'height');
    let width = getMarkupValue(rect.width, size, scalar, 'width');
    let height = getMarkupValue(rect.height, size, scalar, 'height');
    let right = getMarkupValue(rect.right, size, scalar, 'width');
    let bottom = getMarkupValue(rect.bottom, size, scalar, 'height');

    if (!isDefined(top)) {
        if (isDefined(height) && isDefined(bottom)) {
            top = size.height - height - bottom;
        }
        else {
            top = bottom;
        }
    }
    
    if (!isDefined(left)) {
        if (isDefined(width) && isDefined(right)) {
            left = size.width - width - right;
        }
        else {
            left = right;
        }
    }
    
    if (!isDefined(width)) {
        if (isDefined(left) && isDefined(right)) {
            width = size.width - left - right;
        }
        else {
            width = 0;
        }
    }

    if (!isDefined(height)) {
        if (isDefined(top) && isDefined(bottom)) {
            height = size.height - top - bottom;
        }
        else {
            height = 0;
        }
    }

    return {
        x: left || 0,
        y: top || 0,
        width: width || 0,
        height: height || 0
    }

};
//#endif

const pointsToPathShape = points => points
    .map((point, index) => `${index === 0 ? 'M' : 'L'} ${point.x} ${point.y}`)
    .join(' ');

//#if !_EXCLUDE_MARKUP_UTIL

const setAttributes = (element, attr) => Object.keys(attr).forEach(key => element.setAttribute(key, attr[key]));

const ns$1 = 'http://www.w3.org/2000/svg';
const svg = (tag, attr) => {
    const element = document.createElementNS(ns$1, tag);
    if (attr) {
        setAttributes(element, attr);
    }
    return element;
};

const updateRect$1 = (element) => setAttributes(element, {
    ...element.rect,
    ...element.styles,
});

const updateEllipse = (element) => {
    const cx = element.rect.x + (element.rect.width * .5);
    const cy = element.rect.y + (element.rect.height * .5);
    const rx = element.rect.width * .5;
    const ry = element.rect.height * .5;
    return setAttributes(element, {
        cx, cy, rx, ry,
        ...element.styles,
    });
};

const IMAGE_FIT_STYLE = {
    'contain': 'xMidYMid meet',
    'cover': 'xMidYMid slice'
};

const updateImage = (element, markup) => {
    setAttributes(element, {
        ...element.rect,
        ...element.styles,
        preserveAspectRatio: IMAGE_FIT_STYLE[markup.fit] || 'none'
    });
};

const TEXT_ANCHOR = {
    'left': 'start',
    'center': 'middle',
    'right': 'end'
};

const updateText = (element, markup, size, scale) => {

    const fontSize = getMarkupValue(markup.fontSize, size, scale);
    const fontFamily = markup.fontFamily || 'sans-serif';
    const fontWeight = markup.fontWeight || 'normal';
    const textAlign = TEXT_ANCHOR[markup.textAlign] || 'start';

    setAttributes(element, {
        ...element.rect,
        ...element.styles,
        'stroke-width': 0,
        'font-weight': fontWeight,
        'font-size': fontSize,
        'font-family': fontFamily,
        'text-anchor': textAlign
    });

    // update text
    if (element.text !== markup.text) {
        element.text = markup.text;
        element.textContent = markup.text.length ? markup.text : ' ';
    }
};

const updateLine = (element, markup, size, scale) => {

    setAttributes(element, {
        ...element.rect,
        ...element.styles,
        fill: 'none'
    });

    const line = element.childNodes[0];
    const begin = element.childNodes[1];
    const end = element.childNodes[2];
    const hitbox = element.childNodes[3];

    const origin = element.rect;

    const target = {
        x: element.rect.x + element.rect.width,
        y: element.rect.y + element.rect.height
    };

    setAttributes(line, {
        x1: origin.x,
        y1: origin.y,
        x2: target.x,
        y2: target.y
    });

    setAttributes(hitbox, {
        x1: origin.x,
        y1: origin.y,
        x2: target.x,
        y2: target.y,
        // style: `stroke-width: 40; stroke-opacity: 0;`
    });

    if (!markup.lineDecoration) return;

    begin.style.display = 'none';
    end.style.display = 'none';

    const v = vectorNormalize$1({
        x: target.x - origin.x,
        y: target.y - origin.y
    });

    const l = getMarkupValue(.05, size, scale);

    if (markup.lineDecoration.indexOf('arrow-begin') !== -1) {

        const arrowBeginRotationPoint = vectorMultiply$1(v, l);
        const arrowBeginCenter = vectorAdd$1(origin, arrowBeginRotationPoint);
        const arrowBeginA = vectorRotate$1(origin, 2, arrowBeginCenter);
        const arrowBeginB = vectorRotate$1(origin,-2, arrowBeginCenter);
    
        setAttributes(begin, {
            style: 'display:block;',
            d: `M${arrowBeginA.x},${arrowBeginA.y} L${origin.x},${origin.y} L${arrowBeginB.x},${arrowBeginB.y}`
        });
    }

    if (markup.lineDecoration.indexOf('arrow-end') !== -1) {
            
        const arrowEndRotationPoint = vectorMultiply$1(v, -l);
        const arrowEndCenter = vectorAdd$1(target, arrowEndRotationPoint);
        const arrowEndA = vectorRotate$1(target, 2, arrowEndCenter);
        const arrowEndB = vectorRotate$1(target,-2, arrowEndCenter);

        setAttributes(end, {
            style: 'display:block;',
            d: `M${arrowEndA.x},${arrowEndA.y} L${target.x},${target.y} L${arrowEndB.x},${arrowEndB.y}`
        });

    }

};

const updatePath = (element, markup, size, scale) => {

    setAttributes(element, {
        ...element.styles,
        fill: 'none'
    });

    const line = element.childNodes[0];
    const hitbox = element.childNodes[1];

    const d = pointsToPathShape(markup.points.map(point => ({
        x: getMarkupValue(point.x, size, scale, 'width'),
        y: getMarkupValue(point.y, size, scale, 'height')
    })));

    setAttributes(line, { d });
    setAttributes(hitbox, { d });

};

const createShape = (node) => (markup) => svg(node, { id: markup.id });

const createImage = (markup) => {
    const shape = svg('image', {
        id: markup.id,
        'stroke-linecap': 'round',
        'stroke-linejoin': 'round',
        'opacity': '0'
    });
    shape.onload = () => {
        shape.setAttribute('opacity', markup.opacity || 1);
    };
    shape.setAttributeNS('http://www.w3.org/1999/xlink', 'xlink:href', markup.src);
    return shape;
};

const createLine = (markup) => {

    const shape = svg('g', {
        id: markup.id,
        'stroke-linecap':'round',
        'stroke-linejoin':'round'
    });

    const line = svg('line');
    shape.appendChild(line);

    const begin = svg('path');
    shape.appendChild(begin);

    const end = svg('path');
    shape.appendChild(end);

    const hitbox = svg('line', {
        style: `stroke-width: 40; stroke-opacity: 0;`
    });
    shape.appendChild(hitbox);

    return shape;
};

const createPath = (markup) => {

    const shape = svg('g', {
        id: markup.id
    });

    const line = svg('path');
    shape.appendChild(line);

    const hitbox = svg('path', {
        style: `stroke-width: 40; stroke-opacity: 0;`
    });
    shape.appendChild(hitbox);

    return shape;
};

const CREATE_TYPE_ROUTES = {
    image: createImage,
    rect: createShape('rect'),
    ellipse: createShape('ellipse'),
    text: createShape('text'),
    path: createPath,
    line: createLine,
};

const UPDATE_TYPE_ROUTES = {
    rect: updateRect$1,
    ellipse: updateEllipse,
    image: updateImage,
    text: updateText,
    path: updatePath,
    line: updateLine
};

const createMarkupByType = (type, markup) => CREATE_TYPE_ROUTES[type](markup);

const updateMarkupByType = (element, type, markup, size, scale) => {
    if (type !== 'path') {
        element.rect = getMarkupRect(markup, size, scale);
    }
    element.styles = getMarkupStyles(markup, size, scale);
    UPDATE_TYPE_ROUTES[type](element, markup, size, scale);
};
//#endif

const sortMarkupByZIndex = (a, b) => {
    if (a[1].zIndex > b[1].zIndex) {
        return 1;
    }
    if (a[1].zIndex < b[1].zIndex) {
        return -1;
    }
    return 0;
};

//#if !_EXCLUDE_TRANSFORM_IMAGE
const cropSVG = (blob, crop, markup, options) => new Promise(resolve => {

    const { background = null } = options;

    // load blob contents and wrap in crop svg
    const fr = new FileReader();
    fr.onloadend = () => {

        // get svg text
        const text = fr.result;

        // create element with svg and get size
        const original = document.createElement('div');
        original.style.cssText = `position:absolute;pointer-events:none;width:0;height:0;visibility:hidden;`;
        original.innerHTML = text;
        const originalNode = original.querySelector('svg');
        document.body.appendChild(original);

        // request bounding box dimensions
        const bBox = originalNode.getBBox();
        original.parentNode.removeChild(original);

        // get title
        const titleNode = original.querySelector('title');

        // calculate new heights and widths
        const viewBoxAttribute = originalNode.getAttribute('viewBox') || '';
        const widthAttribute = originalNode.getAttribute('width') || '';
        const heightAttribute = originalNode.getAttribute('height') || '';
        let width = parseFloat(widthAttribute) || null;
        let height = parseFloat(heightAttribute) || null;
        const widthUnits = (widthAttribute.match(/[a-z]+/) || [])[0] || '';
        const heightUnits = (heightAttribute.match(/[a-z]+/) || [])[0] || '';

        // create new size
        const viewBoxList = viewBoxAttribute.split(' ').map(parseFloat);
        const viewBox = viewBoxList.length ? {
            x: viewBoxList[0],
            y: viewBoxList[1],
            width: viewBoxList[2],
            height: viewBoxList[3]
        } : bBox;

        let imageWidth = width != null ? width : viewBox.width;
        let imageHeight = height != null ? height : viewBox.height;

        originalNode.style.overflow = 'visible';
        originalNode.setAttribute('width', imageWidth);
        originalNode.setAttribute('height', imageHeight);

        // markup
        let markupSVG = '';
        if (markup && markup.length) {
            const size = {
                width: imageWidth,
                height: imageHeight
            };
            markupSVG = markup.sort(sortMarkupByZIndex).reduce((prev, shape) => {
                const el = createMarkupByType(shape[0], shape[1]);
                updateMarkupByType(el, shape[0], shape[1], size);
                el.removeAttribute('id');
                if (el.getAttribute('opacity') === 1) {
                    el.removeAttribute('opacity');
                }
                return prev + '\n' + el.outerHTML + '\n';
            }, '');
            markupSVG = `\n\n<g>${markupSVG.replace(/&nbsp;/g,' ')}</g>\n\n`;
        }        
        
        const aspectRatio = crop.aspectRatio || imageHeight / imageWidth;
        
        const canvasWidth = imageWidth;
        const canvasHeight = canvasWidth * aspectRatio;

        const shouldLimit = typeof crop.scaleToFit === 'undefined' || crop.scaleToFit;

        const canvasZoomFactor = getImageRectZoomFactor(
            {
                width: imageWidth, 
                height: imageHeight
            }, 
            getCenteredCropRect(
                {
                    width: canvasWidth, 
                    height: canvasHeight
                },
                aspectRatio
            ),
            crop.rotation,
            shouldLimit ? crop.center : {
                x: .5,
                y: .5
            }
        );

        const scale = crop.zoom * canvasZoomFactor;

        const rotation = crop.rotation * (180 / Math.PI);

        const canvasCenter = {
            x: canvasWidth * .5,
            y: canvasHeight * .5
        };

        const imageOffset = {
            x: canvasCenter.x - (imageWidth * crop.center.x),
            y: canvasCenter.y - (imageHeight * crop.center.y)
        };

        const cropTransforms = [

            // rotate
            `rotate(${rotation} ${canvasCenter.x} ${canvasCenter.y})`,
            
            // scale
            `translate(${canvasCenter.x} ${canvasCenter.y})`,
            `scale(${scale})`,
            `translate(${-canvasCenter.x} ${-canvasCenter.y})`,
           
            // offset
            `translate(${imageOffset.x} ${imageOffset.y})`,
             
        ];

        const flipTransforms = [
            `scale(${crop.flip.horizontal ? -1 : 1} ${crop.flip.vertical ? -1 : 1})`,
            `translate(${crop.flip.horizontal ? -imageWidth : 0} ${crop.flip.vertical ? -imageHeight : 0})`
        ];
        
        // crop
        const transformed = `<?xml version="1.0" encoding="UTF-8"?>
<svg width="${canvasWidth}${widthUnits}" height="${canvasHeight}${heightUnits}" 
viewBox="0 0 ${canvasWidth} ${canvasHeight}" ${background ? 'style="background:' + background + '" ' : ''}
preserveAspectRatio="xMinYMin"
xmlns:xlink="http://www.w3.org/1999/xlink"
xmlns="http://www.w3.org/2000/svg">
<!-- Generated by PQINA - https://pqina.nl/ -->
<title>${ titleNode ? titleNode.textContent : '' }</title>
<g transform="${cropTransforms.join(' ')}">
<g transform="${flipTransforms.join(' ')}">
${originalNode.outerHTML}${markupSVG}
</g>
</g>
</svg>`;

        // create new svg file
        resolve(transformed);
    };

    fr.readAsText(blob);
});
//#endif

const objectToImageData = obj => {
    let imageData;
    try {
        imageData = new ImageData(obj.width, obj.height);
    } catch (e) {
        // IE + Old EDGE (tested on 12)
        const canvas = document.createElement('canvas');
        imageData = canvas
            .getContext('2d')
            .createImageData(obj.width, obj.height);
    }
    imageData.data.set(obj.data);
    return imageData;
};

//#if !(_EXCLUDE_FILTER_UTIL && _EXCLUDE_TRANSFORM_IMAGE)

/* javascript-obfuscator:disable */
const TransformWorker = () => {

    // maps transform types to transform functions
    const TRANSFORMS = { resize, filter };

    // applies all image transforms to the image data array
    const applyTransforms = (transforms, imageData) => {
        transforms.forEach(transform => {
            imageData = TRANSFORMS[transform.type](imageData, transform.data);
        });
        return imageData;
    };

    // transform image hub
    const transform = (data, cb) => {

        let transforms = data.transforms;

        // if has filter and has resize, move filter to resize operation
        let filterTransform = null;
        transforms.forEach(transform => {
            if (transform.type === 'filter') {
                filterTransform = transform;
            }
        });
        if (filterTransform) {

            // find resize
            let resizeTransform = null;
            transforms.forEach(transform => {
                if (transform.type === 'resize') {
                    resizeTransform = transform;
                }
            });

            if (resizeTransform) {

                // update resize operation
                resizeTransform.data.matrix = filterTransform.data;

                // remove filter
                transforms = transforms.filter(transform => transform.type !== 'filter');
            }

        }

        cb(applyTransforms(transforms, data.imageData));
    };

    // eslint-disable-next-line no-restricted-globals
    self.onmessage = e => {
        transform(e.data.message, response => {
            // eslint-disable-next-line no-restricted-globals
            self.postMessage({ id: e.data.id, message: response }, [
                response.data.buffer
            ]);
        });
    };

    const br = 1.0;
    const bg = 1.0;
    const bb = 1.0;
    function applyFilterMatrix(index, data, m) {

        const ir = data[index] / 255; 
        const ig = data[index + 1] / 255;
        const ib = data[index + 2] / 255; 
        const ia = data[index + 3] / 255;

        const mr = (ir * m[0]) +  (ig * m[1]) + (ib * m[2]) + (ia * m[3]) + m[4];
        const mg = (ir * m[5]) +  (ig * m[6]) + (ib * m[7]) + (ia * m[8]) + m[9];
        const mb = (ir * m[10]) +  (ig * m[11]) + (ib * m[12]) + (ia * m[13]) + m[14];
        const ma = (ir * m[15]) +  (ig * m[16]) + (ib * m[17]) + (ia * m[18]) + m[19];

        const or = Math.max(0, mr * ma) + (br * (1.0 - ma));
        const og = Math.max(0, mg * ma) + (bg * (1.0 - ma));
        const ob = Math.max(0, mb * ma) + (bb * (1.0 - ma));

        data[index] = Math.max(0.0, Math.min(1.0, or)) * 255;
        data[index + 1] = Math.max(0.0, Math.min(1.0, og)) * 255;
        data[index + 2] = Math.max(0.0, Math.min(1.0, ob)) * 255;
    }

    const identityMatrix = self.JSON.stringify([1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 1, 0]);
    function isIdentityMatrix(filter) {
        return self.JSON.stringify(filter || []) === identityMatrix;
    }

    function filter(imageData, matrix) {

        if (!matrix || isIdentityMatrix(matrix)) return imageData;

        const data = imageData.data;
        const l = data.length;

        const m11 = matrix[0];
        const m12 = matrix[1];
        const m13 = matrix[2];
        const m14 = matrix[3];
        const m15 = matrix[4];

        const m21 = matrix[5];
        const m22 = matrix[6];
        const m23 = matrix[7];
        const m24 = matrix[8];
        const m25 = matrix[9];
        
        const m31 = matrix[10];
        const m32 = matrix[11];
        const m33 = matrix[12];
        const m34 = matrix[13];
        const m35 = matrix[14];

        const m41 = matrix[15];
        const m42 = matrix[16];
        const m43 = matrix[17];
        const m44 = matrix[18];
        const m45 = matrix[19];

        let index=0, r=0.0, g=0.0, b=0.0, a=0.0, mr=0.0, mg=0.0, mb=0.0, ma=0.0, or=0.0, og=0.0, ob=0.0;

        for (; index<l; index+=4) {

            r = data[index] / 255;
            g = data[index + 1] / 255;
            b = data[index + 2] / 255;
            a = data[index + 3] / 255;

            mr = (r * m11) + (g * m12) + (b * m13) + (a * m14) + (m15);
            mg = (r * m21) + (g * m22) + (b * m23) + (a * m24) + (m25);
            mb = (r * m31) + (g * m32) + (b * m33) + (a * m34) + (m35);
            ma = (r * m41) + (g * m42) + (b * m43) + (a * m44) + (m45);

            or = Math.max(0, mr * ma) + (br * (1.0 - ma));
            og = Math.max(0, mg * ma) + (bg * (1.0 - ma));
            ob = Math.max(0, mb * ma) + (bb * (1.0 - ma));

            data[index] = Math.max(0.0, Math.min(1.0, or)) * 255;
            data[index + 1] = Math.max(0.0, Math.min(1.0, og)) * 255;
            data[index + 2] = Math.max(0.0, Math.min(1.0, ob)) * 255;
            // don't update alpha value
        }

        return imageData;
    }

    function resize(imageData, data) {

        let { mode = 'contain', upscale = false, width, height, matrix } = data;

        // test if is identity matrix
        matrix = !matrix || isIdentityMatrix(matrix) ? null : matrix;

        // need at least a width or a height
        // also 0 is not a valid width or height
        if (!width && !height) {
            return filter(imageData, matrix);
        }

        // make sure all bounds are set
        if (width === null) {
            width = height;
        }
        else if (height === null) {
            height = width;
        }
        
        if (mode !== 'force') {

            let scalarWidth = width / imageData.width;
            let scalarHeight = height / imageData.height;
            let scalar = 1;
            
            if (mode === 'cover') {
                scalar = Math.max(scalarWidth, scalarHeight);
            }
            else if (mode === 'contain') {
                scalar = Math.min(scalarWidth, scalarHeight);
            }
        
            // if image is too small, exit here with original image
            if (scalar > 1 && upscale === false) {
                return filter(imageData, matrix);
            }

            width = imageData.width * scalar;
            height = imageData.height * scalar;
        }

        const originWidth = imageData.width;
        const originHeight = imageData.height;
        const targetWidth = Math.round(width);
        const targetHeight = Math.round(height);
        const inputData = imageData.data;
        const outputData = new Uint8ClampedArray(targetWidth * targetHeight * 4);
        const ratioWidth = originWidth / targetWidth;
        const ratioHeight = originHeight / targetHeight;
        const ratioWidthHalf = Math.ceil(ratioWidth * .5);
        const ratioHeightHalf = Math.ceil(ratioHeight * .5);

        for (let j=0; j<targetHeight; j++) {
            for (let i=0; i<targetWidth; i++) {
                
                let x2 = (i + j * targetWidth) * 4;
                let weight = 0;
                let weights = 0;
                let weightsAlpha = 0;
                let r = 0;
                let g = 0;
                let b = 0;
                let a = 0;
                let centerY = (j + .5) * ratioHeight;

                for (let yy = Math.floor(j * ratioHeight); yy < (j + 1) * ratioHeight; yy++) {

                    let dy = Math.abs(centerY - (yy + .5)) / ratioHeightHalf;
                    let centerX = (i + .5) * ratioWidth;
                    let w0 = dy * dy;
                    
                    for (let xx = Math.floor(i * ratioWidth); xx < (i + 1) * ratioWidth; xx++) {

                        let dx = Math.abs(centerX - (xx + .5)) / ratioWidthHalf;
                        let w = Math.sqrt(w0 + dx * dx);

                        if (w >= -1 && w <= 1) {
                            
                            weight = 2 * w * w * w - 3 * w * w + 1;

                            if (weight > 0) {
                                dx = 4 * (xx + yy * originWidth);
                                
                                let ref = inputData[dx + 3];
                                a += weight * ref;
                                weightsAlpha += weight;
                                
                                if (ref < 255) {
                                    weight = weight * ref / 250;
                                }
                                
                                r += weight * inputData[dx];
                                g += weight * inputData[dx + 1];
                                b += weight * inputData[dx + 2];
                                weights += weight;
                            }
                        }
                    }
                }

                outputData[x2] = r / weights;
                outputData[x2 + 1] = g / weights;
                outputData[x2 + 2] = b / weights;
                outputData[x2 + 3] = a / weightsAlpha;

                matrix && applyFilterMatrix(x2, outputData, matrix);
            }
        }

        return {
            data: outputData,
            width: targetWidth,
            height: targetHeight
        }
        
    }

};
/* javascript-obfuscator:enable */

//#endif

const correctOrientation = (view, offset) => {

    // Missing 0x45786966 Marker? No Exif Header, stop here
    if (view.getUint32(offset + 4, false) !== 0x45786966) return;

    // next byte!
    offset += 4;

    // First 2bytes defines byte align of TIFF data. 
    // If it is 0x4949="I I", it means "Intel" type byte align
    const intelByteAligned = view.getUint16(offset += 6, false) === 0x4949;
    offset += view.getUint32(offset + 4, intelByteAligned);

    const tags = view.getUint16(offset, intelByteAligned);
    offset += 2;

    // find Orientation tag
    for (let i=0; i<tags; i++) {
        if (view.getUint16(offset + (i * 12), intelByteAligned) === 0x0112) {
            view.setUint16(offset + (i * 12) + 8, 1, intelByteAligned);
            return true;
        }
    }
    return false;
};

const readData = (data) => {

    const view = new DataView(data);
    
    // Every JPEG file starts from binary value '0xFFD8', so if it's not present, exit here
    if (view.getUint16(0) !== 0xFFD8) return null;

    let offset = 2; // Start at 2 as we skipped two bytes (FFD8)
    let marker;
    let markerLength;
    let orientationCorrected = false;

    while (offset < view.byteLength) {
        marker = view.getUint16(offset, false);
        markerLength = view.getUint16(offset + 2, false) + 2;

        // Test if is APP and COM markers
        const isData = (marker >= 0xFFE0 && marker <= 0xFFEF) || marker === 0xFFFE;
        if (!isData) {
            break;
        }

        if (!orientationCorrected) {
            orientationCorrected = correctOrientation(view, offset, markerLength);
        }

        if (offset + markerLength > view.byteLength) {
            break;
        }
        
        offset += markerLength;
    }
    return data.slice(0, offset);
};

const getImageHead = (file) => new Promise(resolve => {
    const reader = new FileReader();
    reader.onload = () => resolve(readData(reader.result) || null);
    reader.readAsArrayBuffer(file.slice(0, 256 * 1024));
});

const getBlobBuilder = () => {
    return (window.BlobBuilder =
        window.BlobBuilder ||
        window.WebKitBlobBuilder ||
        window.MozBlobBuilder ||
        window.MSBlobBuilder);
};

const createBlob = (arrayBuffer, mimeType) => {
    const BB = getBlobBuilder();

    if (BB) {
        const bb = new BB();
        bb.append(arrayBuffer);
        return bb.getBlob(mimeType);
    }

    return new Blob([arrayBuffer], {
        type: mimeType
    });
};

const getUniqueId$1 = () =>
    Math.random()
        .toString(36)
        .substr(2, 9);

const createWorker = fn => {
    
    const workerBlob = new Blob(['(', fn.toString(), ')()'], { type: 'application/javascript' });
    const workerURL = URL.createObjectURL(workerBlob);
    const worker = new Worker(workerURL);

    const trips = [];

    return {
        transfer: () => {}, // (message, cb) => {}
        post: (message, cb, transferList) => {
            
            const id = getUniqueId$1();
            trips[id] = cb;

            worker.onmessage = e => {
                const cb = trips[e.data.id];
                if (!cb) return;
                cb(e.data.message);
                delete trips[e.data.id];
            };

            worker.postMessage(
                {
                    id,
                    message
                },
                transferList
            );

        },
        terminate: () => {
            worker.terminate();
            URL.revokeObjectURL(workerURL);
        }
    };
};

const loadImage = (url) =>
    new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => {
            resolve(img);
        };
        img.onerror = e => {
            reject(e);
        };
        img.src = url;
    });

//#if !_EXCLUDE_MARKUP_UTIL
const chain = funcs =>
  funcs.reduce((promise, func) =>
    promise.then(result =>
      func().then(Array.prototype.concat.bind(result))),
      Promise.resolve([]));

const canvasApplyMarkup = (canvas, markup) => new Promise((resolve) => {

    const size = {
        width: canvas.width,
        height: canvas.height
    };

    const ctx = canvas.getContext('2d');

    const drawers = markup.sort(sortMarkupByZIndex).map(item => () => 
        new Promise(resolve => {
            const result = TYPE_DRAW_ROUTES[item[0]](ctx, size, item[1], resolve);
            if (result) resolve();
        })
    );

    chain(drawers).then(() => resolve(canvas));
});

const applyMarkupStyles = (ctx, styles) => {
    ctx.beginPath();
    ctx.lineCap = styles['stroke-linecap'];
    ctx.lineJoin = styles['stroke-linejoin'];
    ctx.lineWidth = styles['stroke-width'];
    if (styles['stroke-dasharray'].length) {
        ctx.setLineDash(styles['stroke-dasharray'].split(','));
    }
    ctx.fillStyle = styles['fill'];
    ctx.strokeStyle = styles['stroke'];
    ctx.globalAlpha = styles.opacity || 1;
};

const drawMarkupStyles = (ctx) => {
    ctx.fill();
    ctx.stroke();
    ctx.globalAlpha = 1;
};

const drawRect = (ctx, size, markup) => {
    const rect = getMarkupRect(markup, size);
    const styles = getMarkupStyles(markup, size);
    applyMarkupStyles(ctx, styles);
    ctx.rect(rect.x, rect.y, rect.width, rect.height);
    drawMarkupStyles(ctx, styles);
    return true;
};

const drawEllipse = (ctx, size, markup) => {
    const rect = getMarkupRect(markup, size);
    const styles = getMarkupStyles(markup, size);
    applyMarkupStyles(ctx, styles);
    
    const x = rect.x, 
    y = rect.y, 
    w = rect.width, 
    h = rect.height, 
    kappa = .5522848,
    ox = (w / 2) * kappa,
    oy = (h / 2) * kappa,
    xe = x + w,
    ye = y + h,
    xm = x + w / 2,
    ym = y + h / 2;
  
    ctx.moveTo(x, ym);
    ctx.bezierCurveTo(x, ym - oy, xm - ox, y, xm, y);
    ctx.bezierCurveTo(xm + ox, y, xe, ym - oy, xe, ym);
    ctx.bezierCurveTo(xe, ym + oy, xm + ox, ye, xm, ye);
    ctx.bezierCurveTo(xm - ox, ye, x, ym + oy, x, ym);

    drawMarkupStyles(ctx, styles);
    return true;
};

const drawImage = (ctx, size, markup, done) => {

    const rect = getMarkupRect(markup, size);
    const styles = getMarkupStyles(markup, size);
    applyMarkupStyles(ctx, styles);

    const image = new Image();
    image.onload = () => {
        if (markup.fit === 'cover') {
            const ar = rect.width / rect.height;
            const width = ar > 1 ? image.width : image.height * ar;
            const height = ar > 1 ? image.width / ar : image.height;
            const x = (image.width * .5) - (width * .5);
            const y = (image.height * .5) - (height * .5);
            ctx.drawImage(image, 
                x, y, width, height,
                rect.x, rect.y, rect.width, rect.height);
        }
        else if (markup.fit === 'contain') {
            const scalar = Math.min(rect.width / image.width, rect.height / image.height);
            const width = scalar * image.width;
            const height = scalar * image.height;
            const x = rect.x + (rect.width * .5) - (width * .5);
            const y = rect.y + (rect.height * .5) - (height * .5);
            ctx.drawImage(image, 
                0, 0, image.width, image.height,
                x, y, width, height);
        }
        else {
            ctx.drawImage(image, 
                0, 0, image.width, image.height,
                rect.x, rect.y, rect.width, rect.height);
        }

        drawMarkupStyles(ctx, styles);
        done();
    };
    image.src = markup.src;
};
    
const drawText = (ctx, size, markup) => {

    const rect = getMarkupRect(markup, size);
    const styles = getMarkupStyles(markup, size);
    applyMarkupStyles(ctx, styles);
    
    const fontSize = getMarkupValue(markup.fontSize, size);
    const fontFamily = markup.fontFamily || 'sans-serif';
    const fontWeight = markup.fontWeight || 'normal';
    const textAlign = markup.textAlign || 'left';

    ctx.font = `${fontWeight} ${fontSize}px ${fontFamily}`;
    ctx.textAlign = textAlign;
    ctx.fillText(markup.text, rect.x, rect.y);

    drawMarkupStyles(ctx, styles);
    return true;
};

const drawPath = (ctx, size, markup) => {

    const styles = getMarkupStyles(markup, size);
    applyMarkupStyles(ctx, styles);
    ctx.beginPath();

    const points = markup.points.map(point => ({
        x: getMarkupValue(point.x, size, 1, 'width'),
        y: getMarkupValue(point.y, size, 1, 'height')
    }));
    
    ctx.moveTo(points[0].x, points[0].y);
    const l = points.length;
    for (let i=1;i<l;i++) {
        ctx.lineTo(points[i].x, points[i].y);
    }
    
    drawMarkupStyles(ctx, styles);
    return true;
};

const drawLine = (ctx, size, markup) => {

    const rect = getMarkupRect(markup, size);
    const styles = getMarkupStyles(markup, size);
    applyMarkupStyles(ctx, styles);
    
    ctx.beginPath();

    const origin = {
        x: rect.x,
        y: rect.y
    };

    const target = {
        x: rect.x + rect.width,
        y: rect.y + rect.height
    };

    ctx.moveTo(origin.x, origin.y);
    ctx.lineTo(target.x, target.y);
    
    const v = vectorNormalize$1({
        x: target.x - origin.x,
        y: target.y - origin.y
    });

    const l = .04 * Math.min(size.width, size.height);

    if (markup.lineDecoration.indexOf('arrow-begin') !== -1) {

        const arrowBeginRotationPoint = vectorMultiply$1(v, l);
        const arrowBeginCenter = vectorAdd$1(origin, arrowBeginRotationPoint);
        const arrowBeginA = vectorRotate$1(origin, 2, arrowBeginCenter);
        const arrowBeginB = vectorRotate$1(origin,-2, arrowBeginCenter);
        
        ctx.moveTo(arrowBeginA.x, arrowBeginA.y);
        ctx.lineTo(origin.x, origin.y);
        ctx.lineTo(arrowBeginB.x, arrowBeginB.y);
    }
    if (markup.lineDecoration.indexOf('arrow-end') !== -1) {
            
        const arrowEndRotationPoint = vectorMultiply$1(v, -l);
        const arrowEndCenter = vectorAdd$1(target, arrowEndRotationPoint);
        const arrowEndA = vectorRotate$1(target, 2, arrowEndCenter);
        const arrowEndB = vectorRotate$1(target,-2, arrowEndCenter);
    
        ctx.moveTo(arrowEndA.x, arrowEndA.y);
        ctx.lineTo(target.x, target.y);
        ctx.lineTo(arrowEndB.x, arrowEndB.y);
    }

    drawMarkupStyles(ctx, styles);
    return true;
};

const TYPE_DRAW_ROUTES = {
    rect: drawRect,
    ellipse: drawEllipse,
    image: drawImage,
    text: drawText,
    line: drawLine,
    path: drawPath
};
//#endif

const imageDataToCanvas = (imageData) => {
    const image = document.createElement('canvas');
    image.width = imageData.width;
    image.height = imageData.height;
    const ctx = image.getContext('2d');
    ctx.putImageData(imageData, 0, 0);
    return image;
};

//#if !_EXCLUDE_TRANSFORM_IMAGE
const transformImage = (file, instructions, options = {}) => new Promise((resolve, reject) => {

    // if the file is not an image we do not have any business transforming it
    if (!file || !isImage(file)) return reject({ status: 'not an image file', file });

    // get separate options for easier use
    const { stripImageHead, beforeCreateBlob, afterCreateBlob, canvasMemoryLimit } = options;

    // get crop
    const { crop, size, filter, markup, output } = instructions;

    // get exif orientation
    const orientation = instructions.image && instructions.image.orientation ? Math.max(1, Math.min(8, instructions.image.orientation)) : null;

    // compression quality 0 => 100
    const qualityAsPercentage = output && output.quality;
    const quality = qualityAsPercentage === null ? null : qualityAsPercentage / 100;

    // output format
    const type = output && output.type || null;

    // background color
    const background = output && output.background || null;

    // get transforms
    const transforms = [];

    // add resize transforms if set
    if (size && (typeof size.width === 'number' || typeof size.height === 'number')) {
        transforms.push({ type: 'resize', data: size });
    }

    // add filters
    if (filter && filter.length === 20) {
        transforms.push({ type: 'filter', data: filter });
    }

    // resolves with supplied blob
    const resolveWithBlob = blob => {
        const promisedBlob = afterCreateBlob ? afterCreateBlob(blob) : blob;
        Promise.resolve(promisedBlob).then(resolve);
    };

    // done
    const toBlob = (imageData, options) => {
        const canvas = imageDataToCanvas(imageData);
        const promisedCanvas = markup.length ? canvasApplyMarkup(canvas, markup) : canvas;
        Promise.resolve(promisedCanvas).then(canvas => {
            canvasToBlob(canvas, options, beforeCreateBlob)
            .then(blob => {

                // force release of canvas memory
                canvasRelease(canvas);
        
                // remove image head (default)
                if (stripImageHead) return resolveWithBlob(blob);

                // try to copy image head
                getImageHead(file).then(imageHead => {

                    // re-inject image head EXIF info in case of JPEG, as the image head is removed by canvas export
                    if (imageHead !== null) {
                        blob = new Blob([imageHead, blob.slice(20)], { type: blob.type });
                    }
                    
                    // done!
                    resolveWithBlob(blob);
                });
            })
            .catch(reject);
        });
    };

    // if this is an svg and we want it to stay an svg
    if (/svg/.test(file.type) && type === null) {
        return cropSVG(file, crop, markup, { background }).then(text => {
            resolve(
                createBlob(text, 'image/svg+xml')
            );
        });
    }

    // get file url
    const url = URL.createObjectURL(file);

    // turn the file into an image
    loadImage(url)
        .then(image => {

            // url is no longer needed
            URL.revokeObjectURL(url);

            // draw to canvas and start transform chain
            const imageData = imageToImageData(image, orientation, crop, { canvasMemoryLimit, background });

            // determine the format of the blob that we will output
            const outputFormat = {
                quality,
                type: type || file.type
            };

            // no transforms necessary, we done!
            if (!transforms.length) {
                return toBlob(imageData, outputFormat);
            }

            // send to the transform worker to transform the blob on a separate thread
            const worker = createWorker(TransformWorker);
            worker.post(
                {
                    transforms,
                    imageData
                },
                response => {

                    // finish up
                    toBlob(objectToImageData(response), outputFormat);

                    // stop worker
                    worker.terminate();
                },
                [imageData.data.buffer]
            );
        })
        .catch(reject);
});
//#endif

const readExif = (view, offset) => {

    // Missing 0x45786966 Marker? No Exif Header, stop here
    if (view.getUint32(offset += 2, false) !== 0x45786966) return -1;

    // First 2bytes defines byte align of TIFF data. 
    // If it is 0x4949="I I", it means "Intel" type byte align
    const intelByteAligned = view.getUint16(offset += 6, false) === 0x4949;
    offset += view.getUint32(offset + 4, intelByteAligned);
    
    const tags = view.getUint16(offset, intelByteAligned);
    offset += 2;

    // find Orientation tag
    for (let i=0; i<tags; i++) {
        if (view.getUint16(offset + (i * 12), intelByteAligned) === 0x0112) return view.getUint16(offset + (i * 12) + 8, intelByteAligned);
    }
};

const readData$1 = (data) => {

    const view = new DataView(data);
    
    // Every JPEG file starts from binary value '0xFFD8', so if it's not present, exit here
    if (view.getUint16(0, false) != 0xFFD8) return null;

    const length = view.byteLength;
    let offset = 2;
    let marker;

    while (offset < length) 
    {
        if (view.getUint16(offset + 2, false) <= 8) return -1;

        marker = view.getUint16(offset, false);
        offset += 2;

        // Exif Marker found
        if (marker === 0xFFE1) return readExif(view, offset);
        // Invalid marker, exit here
        else if ((marker & 0xFF00) !== 0xFF00) return null;
        // Next!
        else offset += view.getUint16(offset, false);
    }

};

const getImageOrientation = (file) => new Promise(resolve => {
    const reader = new FileReader();
    reader.onload = () => resolve(readData$1(reader.result) || -1);
    reader.readAsArrayBuffer(file.slice(0, 256 * 1024));
});

const Direction = {
    HORIZONTAL: 1,
    VERTICAL: 2
};

const getImageTransformsFromCrop = (crop, stage, image, limitToImageBounds = true) => {
    
    const { center, zoom, aspectRatio } = crop;

    const stageCenter = rectCenter(stage);

    // move to image to center (rotation and scale origin)
    const translation = {
        x: stageCenter.x - (image.width * center.x),
        y: stageCenter.y - (image.height * center.y)
    };

    // get rotation
    const rotation = (Math.PI * 2) + (crop.rotation % (Math.PI * 2));

    // determine stage zoom factor (how far should we zoom in the image based on the crop rectangle)
    const stageScaleFactor = getImageRectZoomFactor(
        image,
        getCenteredCropRect(
            stage, 
            aspectRatio || image.height / image.width
        ),
        rotation,
        limitToImageBounds ? center : {
            x: .5,
            y: .5
        }
    );

    const scale = zoom * stageScaleFactor;

    // update view model
    return {
        origin: {
            x: center.x * image.width,
            y: center.y * image.height
        },
        translation,
        scale,
        rotation: crop.rotation
    };
};

const copyImageTransforms = (transforms) => ({
    origin: {
        ...transforms.origin
    },
    translation: {
        ...transforms.translation
    },
    rotation: transforms.rotation,
    scale: transforms.scale
});

const limitImageTransformsToCropRect = (image, crop, imageTransforms, action) => {

    const { translation, scale, rotation, origin } = imageTransforms;

    const limited = {
        origin: {
            ...origin
        },
        translation: {
            ...translation
        },
        scale,
        rotation: ((Math.PI * 2) + (rotation % (Math.PI * 2)))
    };

    const imageAspectRatio = image.height / image.width;

    const axisAlignedCropRect = getAxisAlignedCropRect(origin, translation, limited.rotation, crop);



    const axisAlignedCropRectCenter = rectCenter(axisAlignedCropRect);

    const axisAlignedCropRectBounds = rectBounds(axisAlignedCropRect);

    const axisAlignedImageRect = getAxisAlignedImageRect(image, imageTransforms);

    const axisAlignedImageRectCenter = rectCenter(axisAlignedImageRect);

    const rectOffset = {
        x: axisAlignedImageRect.x,
        y: axisAlignedImageRect.y
    };

    const rectangleCenter = {
        x: axisAlignedImageRectCenter.x,
        y: axisAlignedImageRectCenter.y
    };

    const cropCenter = {
        x: axisAlignedCropRectCenter.x,
        y: axisAlignedCropRectCenter.y
    };

    const rectFitted = {
        x: rectOffset.x,
        y: rectOffset.y,
        width: axisAlignedImageRect.width,
        height: axisAlignedImageRect.height
    };

    if (!rectFitsInRect(axisAlignedCropRect, axisAlignedImageRect)) {

        // when translating we want to limit the translation to fit the bounding box
        if (action === 'moving') {
            
            if (rectFitted.y > axisAlignedCropRect.y) {
                rectFitted.y = axisAlignedCropRect.y;
            }
            else if (rectFitted.y + rectFitted.height < axisAlignedCropRectBounds.bottom) {
                rectFitted.y = axisAlignedCropRectBounds.bottom - rectFitted.height;
            }
            
            if (rectFitted.x > axisAlignedCropRect.x) {
                rectFitted.x = axisAlignedCropRect.x;
            }
            else if (rectFitted.x + rectFitted.width < axisAlignedCropRectBounds.right) {
                rectFitted.x = axisAlignedCropRectBounds.right - rectFitted.width;
            }

            const minimumAxisAlignedImageRect = getAxisAlignedImageRect(
                image, 
                {
                    ...imageTransforms,
                    scale: limited.scale
                }
            );

            const minimumAxisAlignedImageRectCenter = rectCenter(minimumAxisAlignedImageRect);
            
            rectangleCenter.x = minimumAxisAlignedImageRectCenter.x;
            rectangleCenter.y = minimumAxisAlignedImageRectCenter.y;

            rectOffset.x = minimumAxisAlignedImageRect.x;
            rectOffset.y = minimumAxisAlignedImageRect.y;

            rectFitted.x = rectangleCenter.x - (rectFitted.width * .5);
            rectFitted.y = rectangleCenter.y - (rectFitted.height * .5);
            
            if (rectFitted.y > axisAlignedCropRect.y) {
                rectFitted.y = axisAlignedCropRect.y;
            }
            else if (rectFitted.y + rectFitted.height < axisAlignedCropRectBounds.bottom) {
                rectFitted.y = axisAlignedCropRectBounds.bottom - rectFitted.height;
            }
            
            if (rectFitted.x > axisAlignedCropRect.x) {
                rectFitted.x = axisAlignedCropRect.x;
            }
            else if (rectFitted.x + rectFitted.width < axisAlignedCropRectBounds.right) {
                rectFitted.x = axisAlignedCropRectBounds.right - rectFitted.width;
            }

            const v = {
                x: rectFitted.x - rectOffset.x,
                y: rectFitted.y - rectOffset.y
            };

            const r = {
                x: v.x * Math.cos(limited.rotation) - v.y * Math.sin(limited.rotation),
                y: v.x * Math.sin(limited.rotation) + v.y * Math.cos(limited.rotation)
            };

            limited.translation.x += r.x;
            limited.translation.y += r.y;

        }
        else if (action === 'resizing') {
        
            if (axisAlignedImageRect.width < axisAlignedCropRect.width) {
                rectFitted.width = axisAlignedCropRect.width;
                rectFitted.height = rectFitted.width * imageAspectRatio;
                if (rectFitted.height < axisAlignedCropRect.height) {
                    rectFitted.height = axisAlignedCropRect.height;
                    rectFitted.width = rectFitted.height / imageAspectRatio;
                }
            }
            
            if (axisAlignedImageRect.height < axisAlignedCropRect.height) {
                rectFitted.height = axisAlignedCropRect.height;
                rectFitted.width = rectFitted.height / imageAspectRatio;
                if (rectFitted.width < axisAlignedCropRect.width) {
                    rectFitted.width = axisAlignedCropRect.width;
                    rectFitted.height = rectFitted.width * imageAspectRatio;
                }
            }

            rectFitted.x = rectangleCenter.x - (rectFitted.width * .5);
            rectFitted.y = rectangleCenter.y - (rectFitted.height * .5);

            if (rectFitted.y > axisAlignedCropRect.y) {
                rectFitted.y = axisAlignedCropRect.y;
            }
            else if (rectFitted.y + rectFitted.height < axisAlignedCropRectBounds.bottom) {
                rectFitted.y = axisAlignedCropRectBounds.bottom - rectFitted.height;
            }
            
            if (rectFitted.x > axisAlignedCropRect.x) {
                rectFitted.x = axisAlignedCropRect.x;
            }
            else if (rectFitted.x + rectFitted.width < axisAlignedCropRectBounds.right) {
                rectFitted.x = axisAlignedCropRectBounds.right - rectFitted.width;
            }

            limited.scale = getImageRectZoomFactor(
                image,
                crop,
                limited.rotation,
                {
                    x: (cropCenter.x - rectFitted.x) / rectFitted.width,
                    y: (cropCenter.y - rectFitted.y) / rectFitted.height
                }
            );

            const minimumAxisAlignedImageRect = getAxisAlignedImageRect(
                image, 
                {
                    ...imageTransforms,
                    scale: limited.scale
                }
            );

            const minimumAxisAlignedImageRectCenter = rectCenter(minimumAxisAlignedImageRect);
            
            rectangleCenter.x = minimumAxisAlignedImageRectCenter.x;
            rectangleCenter.y = minimumAxisAlignedImageRectCenter.y;

            rectOffset.x = minimumAxisAlignedImageRect.x;
            rectOffset.y = minimumAxisAlignedImageRect.y;

            rectFitted.x = rectangleCenter.x - (rectFitted.width * .5);
            rectFitted.y = rectangleCenter.y - (rectFitted.height * .5);
            
            if (rectFitted.y > axisAlignedCropRect.y) {
                rectFitted.y = axisAlignedCropRect.y;
            }
            else if (rectFitted.y + rectFitted.height < axisAlignedCropRectBounds.bottom) {
                rectFitted.y = axisAlignedCropRectBounds.bottom - rectFitted.height;
            }
            
            if (rectFitted.x > axisAlignedCropRect.x) {
                rectFitted.x = axisAlignedCropRect.x;
            }
            else if (rectFitted.x + rectFitted.width < axisAlignedCropRectBounds.right) {
                rectFitted.x = axisAlignedCropRectBounds.right - rectFitted.width;
            }

            const v = {
                x: rectFitted.x - rectOffset.x,
                y: rectFitted.y - rectOffset.y
            };

            const r = {
                x: v.x * Math.cos(limited.rotation) - v.y * Math.sin(limited.rotation),
                y: v.x * Math.sin(limited.rotation) + v.y * Math.cos(limited.rotation)
            };

            limited.translation.x += r.x;
            limited.translation.y += r.y;

        }
        else if (action === 'rotating') {

            let scale = false;

            if (rectFitted.y > axisAlignedCropRect.y) {
                const diff = rectFitted.y - axisAlignedCropRect.y;
                rectFitted.y = axisAlignedCropRect.y;
                rectFitted.height += diff * 2;
                scale = true;
            }
            
            if (rectFitted.y + rectFitted.height < axisAlignedCropRectBounds.bottom) {
                const diff = axisAlignedCropRectBounds.bottom - (rectFitted.y + rectFitted.height);
                rectFitted.y = axisAlignedCropRectBounds.bottom - rectFitted.height;
                rectFitted.height += diff * 2;
                scale = true;
            }

            if (rectFitted.x > axisAlignedCropRect.x) {
                const diff = rectFitted.x - axisAlignedCropRect.x;
                rectFitted.x = axisAlignedCropRect.x;
                rectFitted.width += diff * 2;
                scale = true;
            }
            
            if (rectFitted.x + rectFitted.width < axisAlignedCropRectBounds.right) {
                const diff = axisAlignedCropRectBounds.right - (rectFitted.x + rectFitted.width);
                rectFitted.x = axisAlignedCropRectBounds.right - rectFitted.width;
                rectFitted.width += diff * 2;
                scale = true;
            }

            if (scale) {

                limited.scale = getImageRectZoomFactor(
                    image,
                    crop,
                    limited.rotation,
                    {   
                        x: (cropCenter.x - axisAlignedImageRect.x) / axisAlignedImageRect.width,
                        y: (cropCenter.y - axisAlignedImageRect.y) / axisAlignedImageRect.height
                    }
                );

            }
            
        }
        
    }
    
    return {
        ...limited,
        rotation: imageTransforms.rotation
    }

};

const getTransformOrigin = (image, crop, imageTransforms) => {

    const { origin, translation, scale } = imageTransforms;

    const rotation = (Math.PI * 2) + (imageTransforms.rotation % (Math.PI * 2));

    const rotatedImageRectPivot = {
        x: origin.x + translation.x,
        y: origin.y + translation.y
    };

    const axisAlignedCropRect = getAxisAlignedCropRect(origin, translation, rotation, crop);

    const axisAlignedImageRect = getAxisAlignedImageRect(image, imageTransforms);

    const axisAlignedImageRectCorners = rectCorners(axisAlignedImageRect);

    const axisAlignedImageRectCenter = rectCenter(axisAlignedImageRect);

    const _tl = vectorRotate(axisAlignedImageRectCorners.tl, rotation, rotatedImageRectPivot);
    const _br = vectorRotate(axisAlignedImageRectCorners.br, rotation, rotatedImageRectPivot);

    const rotatedImageCenter = {
        x: _tl.x + ((_br.x - _tl.x) * .5),
        y: _tl.y + ((_br.y - _tl.y) * .5)
    };

    const finalImageRect = rectTranslate(axisAlignedImageRect, {
        x: rotatedImageCenter.x - axisAlignedImageRectCenter.x,
        y: rotatedImageCenter.y - axisAlignedImageRectCenter.y
    });

    const finalCropRect = rectTranslate(axisAlignedCropRect, {
        x: rotatedImageCenter.x - axisAlignedImageRectCenter.x,
        y: rotatedImageCenter.y - axisAlignedImageRectCenter.y
    });

    const finalCropRectCenter = rectCenter(finalCropRect);

    const finalImageOffset = {
        x: finalImageRect.x,
        y: finalImageRect.y
    };

    const finalImageSize = {
        width: finalImageRect.width,
        height: finalImageRect.height
    };

    const center = {
        x: (finalCropRectCenter.x - finalImageOffset.x) / finalImageSize.width,
        y: (finalCropRectCenter.y - finalImageOffset.y) / finalImageSize.height
    };

    const newOrigin = {
        x: center.x * image.width,
        y: center.y * image.height
    };

    const invertedScalar = 1 - scale;

    const originScaled = {
        x: newOrigin.x * invertedScalar,
        y: newOrigin.y * invertedScalar
    };

    const finalImageCenter = {
        x: finalImageOffset.x + (finalImageSize.width * .5),
        y: finalImageOffset.y + (finalImageSize.height * .5)
    };

    const finalImageOrigin = {
        x: finalImageOffset.x + (finalImageSize.width * center.x),
        y: finalImageOffset.y + (finalImageSize.height * center.y)
    };

    const finalImageTopLeftCenterRotated = vectorRotate(finalImageOffset, rotation, finalImageCenter);
    
    const finalImageTopLeftOriginRotated = vectorRotate(finalImageOffset, rotation, finalImageOrigin);

    const finalImageRotationOffset = {
        x: finalImageTopLeftCenterRotated.x - finalImageTopLeftOriginRotated.x,
        y: finalImageTopLeftCenterRotated.y - finalImageTopLeftOriginRotated.y
    };

    // the translation of the image on the stage, how do we move the image so its aligned with the final rectangle
    return {
        origin: roundVector(newOrigin),
        translation: roundVector({
            x: (finalImageOffset.x - originScaled.x) + finalImageRotationOffset.x,
            y: (finalImageOffset.y - originScaled.y) + finalImageRotationOffset.y
        })
    };
    
};

const EdgeMap = {
    n:(rect) => ({
        x: rect.x + (rect.width * .5),
        y: rect.y
    }),
    e:(rect) => ({
        x: rect.x + rect.width,
        y: rect.y + (rect.height * .5)
    }),
    s:(rect) => ({
        x: rect.x + (rect.width * .5),
        y: rect.y + rect.height
    }),
    w:(rect) => ({
        x: rect.x,
        y: rect.y + (rect.height * .5)
    })
};

const getEdgeCenterCoordinates = (edge, rect) => EdgeMap[edge](rect);

const getImageTransformsFromRect = (image, rect, currentTransforms) => {
    
    // rectangle input (will stay the same)
    const { origin, translation } = currentTransforms;

    // use rounded totation for inner logic
    const rotation = (Math.PI * 2) + (currentTransforms.rotation % (Math.PI * 2));

    // calculate axis aligned bounding box of image
    const axisAlignedImageRect = getAxisAlignedImageRect(image, currentTransforms);

    // point on aligned image rectangle to rotate around
    const rotatedImageRectPivot = {
        x: origin.x + translation.x,
        y: origin.y + translation.y
    };

    const axisAlignedCropRect = getAxisAlignedCropRect(origin, translation, rotation, rect);

    // get bounds so we can determine if crop is breaking out of image rect
    const axisAlignedCropBounds = rectBounds(axisAlignedCropRect);
    const axisAlignedImageBounds = rectBounds(axisAlignedImageRect);
    
    // default image rect transform
    let transformedImageRect = axisAlignedImageRect;

    // move or scale if not inside image bounds
    if (axisAlignedCropBounds.top < axisAlignedImageBounds.top || axisAlignedCropBounds.right > axisAlignedImageBounds.right || axisAlignedCropBounds.bottom > axisAlignedImageBounds.bottom || axisAlignedCropBounds.left < axisAlignedImageBounds.left) {

        const translatedBounds = {
            ...axisAlignedImageBounds
        };

        if (axisAlignedCropBounds.top <= translatedBounds.top) {

            const height = translatedBounds.bottom - translatedBounds.top;
            const width = translatedBounds.right - translatedBounds.left;
            const scalar = Math.max(1, axisAlignedCropRect.height / height);
            const scaledHeight = height * scalar;
            const scaledWidth = width * scalar;
            const diffWidth = scaledWidth - width;

            translatedBounds.bottom = axisAlignedCropBounds.top + scaledHeight;
            translatedBounds.top = axisAlignedCropBounds.top;
            translatedBounds.left -= diffWidth * .5;
            translatedBounds.right += diffWidth * .5;
            
        }

        if (axisAlignedCropBounds.bottom >= translatedBounds.bottom) {

            const height = translatedBounds.bottom - translatedBounds.top;
            const width = translatedBounds.right - translatedBounds.left;
            const scalar = Math.max(1, axisAlignedCropRect.height / height);
            const scaledHeight = height * scalar;
            const scaledWidth = width * scalar;
            const diffWidth = scaledWidth - width;

            translatedBounds.bottom = axisAlignedCropBounds.bottom;
            translatedBounds.top = axisAlignedCropBounds.bottom - scaledHeight;
            translatedBounds.left -= diffWidth * .5;
            translatedBounds.right += diffWidth * .5;
            
        }

        if (axisAlignedCropBounds.left <= translatedBounds.left) {

            const height = translatedBounds.bottom - translatedBounds.top;
            const width = translatedBounds.right - translatedBounds.left;
            const scalar = Math.max(1, axisAlignedCropRect.width / width);
            const scaledHeight = height * scalar;
            const scaledWidth = width * scalar;
            const diffHeight = scaledHeight - height;

            translatedBounds.right = axisAlignedCropBounds.left + scaledWidth;
            translatedBounds.left = axisAlignedCropBounds.left;
            translatedBounds.top -= diffHeight * .5;
            translatedBounds.bottom += diffHeight * .5;

        }

        if (axisAlignedCropBounds.right >= translatedBounds.right) {

            const height = translatedBounds.bottom - translatedBounds.top;
            const width = translatedBounds.right - translatedBounds.left;
            const scalar = Math.max(1, axisAlignedCropRect.width / width);
            const scaledHeight = height * scalar;
            const scaledWidth = width * scalar;
            const diffHeight = scaledHeight - height;

            translatedBounds.right = axisAlignedCropBounds.right;
            translatedBounds.left = axisAlignedCropBounds.right - scaledWidth;
            translatedBounds.top -= diffHeight * .5;
            translatedBounds.bottom += diffHeight * .5;

        }

        transformedImageRect = createRect(
            translatedBounds.left,
            translatedBounds.top,
            translatedBounds.right - translatedBounds.left,
            translatedBounds.bottom - translatedBounds.top
        );

    }

    const transformedImageRectCorners = rectCorners(transformedImageRect);
    const transformedImageRectCenter = rectCenter(transformedImageRect);

    // restore image rotation and update image position
    const _tl = vectorRotate(transformedImageRectCorners.tl, rotation, rotatedImageRectPivot);
    const _br = vectorRotate(transformedImageRectCorners.br, rotation, rotatedImageRectPivot);

    const rotatedImageCenter = {
        x: _tl.x + ((_br.x - _tl.x) * .5),
        y: _tl.y + ((_br.y - _tl.y) * .5)
    };
    
    const finalImageRect = rectTranslate(transformedImageRect, {
        x: rotatedImageCenter.x - transformedImageRectCenter.x,
        y: rotatedImageCenter.y - transformedImageRectCenter.y
    });

    const finalCropRect = rectTranslate(axisAlignedCropRect, {
        x: rotatedImageCenter.x - transformedImageRectCenter.x,
        y: rotatedImageCenter.y - transformedImageRectCenter.y
    });

    const finalCropRectCenter = rectCenter(finalCropRect);

    const finalImageOffset = {
        x: finalImageRect.x,
        y: finalImageRect.y
    };

    const finalImageSize = {
        width: finalImageRect.width,
        height: finalImageRect.height
    };

    // calculate the center of the crop rectangle position relative to the image (value between 0 and 1)
    const center = {
        x: (finalCropRectCenter.x - finalImageOffset.x) / finalImageSize.width,
        y: (finalCropRectCenter.y - finalImageOffset.y) / finalImageSize.height
    };

    // the scale of the image (scale of the final image divided by the original image size)
    const newScale = finalImageSize.width / image.width;

    // calculate image transform origin (this is a position on the unrotated / unscaled image)
    const newOrigin = {
        x: center.x * image.width,
        y: center.y * image.height
    };

    const invertedScalar = 1 - newScale;

    const originScaled = {
        x: newOrigin.x * invertedScalar,
        y: newOrigin.y * invertedScalar
    };

    const finalImageCenter = {
        x: finalImageOffset.x + (finalImageSize.width * .5),
        y: finalImageOffset.y + (finalImageSize.height * .5)
    };
    
    const finalImageOrigin = {
        x: finalImageOffset.x + (finalImageSize.width * center.x),
        y: finalImageOffset.y + (finalImageSize.height * center.y)
    };

    const finalImageTopLeftCenterRotated = vectorRotate(finalImageOffset, rotation, finalImageCenter);
    
    const finalImageTopLeftOriginRotated = vectorRotate(finalImageOffset, rotation, finalImageOrigin);

    const finalImageRotationOffset = {
        x: finalImageTopLeftCenterRotated.x - finalImageTopLeftOriginRotated.x,
        y: finalImageTopLeftCenterRotated.y - finalImageTopLeftOriginRotated.y
    };

    // the translation of the image on the stage, how do we move the image so its aligned with the final rectangle
    const newTranslation = {
        x: (finalImageOffset.x - originScaled.x) + finalImageRotationOffset.x,
        y: (finalImageOffset.y - originScaled.y) + finalImageRotationOffset.y
    };
    

    return {
        origin: newOrigin,
        translation: newTranslation,
        scale: newScale,
        rotation: currentTransforms.rotation
    }
};

const getEdgeTargetRect = (sign, target, direction, anchorCentered, cropBounds, stageBounds, cropAspectRatio, minSize, maxSize) => {

    // ------------------------------------------
    // define outer bounds
    const outerLeft = stageBounds.left;
    const outerRight = stageBounds.right;
    const outerTop = stageBounds.top;
    const outerBottom = stageBounds.bottom;
    const maxWidth = outerRight - outerLeft;
    const maxHeight = outerBottom - outerTop;

    // ------------------------------------------
    // basic crop without limits
    let { left, right, top, bottom } = cropBounds;

    if (direction === Direction.VERTICAL) {
        top = sign.y > 0 ? anchorCentered.y : Math.min(anchorCentered.y, Math.max(target.y, outerTop));
        bottom = sign.y > 0 ? Math.max(anchorCentered.y, Math.min(target.y, outerBottom)) : anchorCentered.y;
        if (cropAspectRatio) {
            const height = bottom - top;
            const width = height / cropAspectRatio;
            left = anchorCentered.x - (width * .5);
            right = anchorCentered.x + (width * .5);
        }
    }
    else {
        left = sign.x > 0 ? anchorCentered.x : Math.min(anchorCentered.x, Math.max(target.x, outerLeft));
        right = sign.x > 0 ? Math.max(anchorCentered.x, Math.min(target.x, outerRight)) : anchorCentered.x;
        if (cropAspectRatio) {
            const width = right - left;
            const height = width * cropAspectRatio;
            top = anchorCentered.y - (height * .5);
            bottom = anchorCentered.y + (height * .5);
        }
    }


    // ------------------------------------------
    // define inner bounds
    const minWidth = minSize.width;
    const minHeight = minSize.height;
    let innerLeft;
    let innerRight;
    let innerTop;
    let innerBottom;
    if (direction === Direction.VERTICAL) {
        innerLeft = anchorCentered.x - (minWidth * .5);
        innerRight = anchorCentered.x + (minWidth * .5);
        if (sign.y < 0) {
            innerTop = anchorCentered.y - minHeight;
            innerBottom = anchorCentered.y;
        }
        else if (sign.y > 0) {
            innerTop = anchorCentered.y;
            innerBottom = anchorCentered.y + minHeight;
        }
    }
    else {
        innerTop = anchorCentered.y - (minHeight * .5);
        innerBottom = anchorCentered.y + (minHeight * .5);
        if (sign.x < 0) {
            innerLeft = anchorCentered.x - minWidth;
            innerRight = anchorCentered.x;
        }
        else if (sign.x > 0) {
            innerLeft = anchorCentered.x;
            innerRight = anchorCentered.x + minWidth;
        }
    }

    // ------------------------------------------
    // is too big
    
    if (cropAspectRatio) {

        if (direction === Direction.VERTICAL) {

            const aspectRatioWidth = Math.min((bottom - top) / cropAspectRatio, maxWidth);
            const aspectRatioHeight = aspectRatioWidth * cropAspectRatio;

            if (left < outerLeft) {
                left = outerLeft;
                right = left + aspectRatioWidth;
            }
            
            if (right > outerRight) {
                right = outerRight;
                left = right - aspectRatioWidth;
            }

            anchorCentered.x = left + (aspectRatioWidth * .5);
            if (sign.y < 0) {
                top = anchorCentered.y - aspectRatioHeight;
            }
            else if (sign.y > 0) {
                bottom = anchorCentered.y + aspectRatioHeight;
            }
        }
        else {

            const aspectRatioHeight = Math.min((right - left) * cropAspectRatio, maxHeight);
            const aspectRatioWidth = aspectRatioHeight / cropAspectRatio;

            if (top < outerTop) {
                top = outerTop;
                bottom = top + aspectRatioHeight;
            }

            if (bottom > outerBottom) {
                bottom = outerBottom;
                top = bottom - aspectRatioHeight;
            }

            anchorCentered.y = top + (aspectRatioHeight * .5);
            if (sign.x < 0) {
                left = anchorCentered.x - aspectRatioWidth;
            }
            else if (sign.x > 0) {
                right = anchorCentered.x + aspectRatioWidth;
            }
        }
    
    }
    
    let free = rectFromBounds({top, right, bottom, left});

    
    // ------------------------------------------
    // is too small

    const limitByAspectRatioHorizontal = () => {
        const aspectRatioHeight = minWidth * cropAspectRatio;
        if (direction === Direction.HORIZONTAL) {
            top = anchorCentered.y - (aspectRatioHeight * .5);
            bottom = anchorCentered.y + (aspectRatioHeight * .5);
        }
        else {
            if (sign.y < 0) {
                bottom = anchorCentered.y;
                top = bottom - aspectRatioHeight;
            }
            else if (sign.y > 0) {
                top = anchorCentered.y;
                bottom = top + aspectRatioHeight;
            }
        }
    };

    const limitByAspectRatioVertical = () => {
        const aspectRatioWidth = minHeight / cropAspectRatio;
        if (direction === Direction.VERTICAL) {
            left = anchorCentered.x - (aspectRatioWidth * .5);
            right = anchorCentered.x + (aspectRatioWidth * .5);
        }
        else {
            if (sign.x < 0) {
                right = anchorCentered.x;
                left = right - aspectRatioWidth;
            }
            else if (sign.x > 0) {
                left = anchorCentered.x;
                right = left + aspectRatioWidth;
            }
        }
    };
    
    if (right < innerRight) {
        right = innerRight;
        left = innerRight - minWidth;
        if (cropAspectRatio) {
            limitByAspectRatioHorizontal();
        }
    }

    if (left > innerLeft) {
        left = innerLeft;
        right = innerLeft + minWidth;
        if (cropAspectRatio) {
            limitByAspectRatioHorizontal();
        }
    }

    if (top > innerTop) {
        top = innerTop;
        bottom = innerTop + minHeight;
        if (cropAspectRatio) {
            limitByAspectRatioVertical();
        }
    }

    if (bottom < innerBottom) {
        bottom = innerBottom;
        top = innerBottom - minHeight;
        if (cropAspectRatio) {
            limitByAspectRatioVertical();
        }
    }

    let maxSizeWidth = maxSize.width;
    let maxSizeHeight = maxSize.height;
    if (cropAspectRatio) {
        if (cropAspectRatio < 1) {
            maxSizeWidth = maxSizeHeight / cropAspectRatio;
        }
        else {
            maxSizeHeight = maxSizeWidth * cropAspectRatio;
        }
    }

    if (right - left > maxSizeWidth) {
        if (sign.x < 0) {
            left = anchorCentered.x - maxSizeWidth;
        }
        else {
            right = anchorCentered.x + maxSizeWidth;
        }
    }

    if (bottom - top > maxSizeHeight) {
        if (sign.y < 0) {
            top = anchorCentered.y - maxSizeHeight;
        }
        else {
            bottom = anchorCentered.y + maxSizeHeight;
        }
    }
    
    

    // ------------------------------------------
    // make sure the rectangle is not zero sized
    if (right - left === 0) {
        if (sign.x > 0) {
            right = anchorCentered.x + 2;
        }
        else {
            left = anchorCentered.x - 2;
        }
    }

    if (bottom - top === 0) {
        if (sign.y > 0) {
            bottom = anchorCentered.y + 2;
        }
        else {
            top = anchorCentered.y - 2;
        }
    }

    // ------------------------------------------
    // make sure the rectangle didn't fall out of view bounds
    if (Math.round(left) < outerLeft || Math.round(right) > outerRight || Math.round(top) < outerTop || Math.round(bottom) > outerBottom) {

        const maxHeight = outerBottom - outerTop;
        const maxWidth = outerRight - outerLeft;

        if (left < outerLeft) {
            left = outerLeft;
            const width = Math.min(right - left, maxWidth);
            right = left + width;
        }
    
        if (right > outerRight) {
            right = outerRight;
            const width = Math.min(right - left, maxWidth);
            left = right - width;
        }
    
        if (top < outerTop) {
            top = outerTop;
            const height = Math.min(bottom - top, maxHeight);
            bottom = top + height;
        }
    
        if (bottom > outerBottom) {
            bottom = outerBottom;
            const height = Math.min(bottom - top, maxHeight);
            top = bottom - height;
        }

        free = rectFromBounds({top, right, bottom, left});
    }

    // turn back into rect
    const limited = rectFromBounds({top, right, bottom, left});

    return {
        free,
        limited
    };
};

const CornerMap = {
    nw:(rect) => ({
        x: rect.x,
        y: rect.y
    }),
    ne:(rect) => ({
        x: rect.x + rect.width,
        y: rect.y
    }),
    se:(rect) => ({
        x: rect.x + rect.width,
        y: rect.y + rect.height
    }),
    sw:(rect) => ({
        x: rect.x,
        y: rect.y + rect.height
    })
};

const getCornerCoordinates = (corner, rect) => CornerMap[corner](rect);

const getCornerTargetRect = (sign, origin, anchor, stage, cropAspectRatio, minSize, maxSize) => {

    const stageBounds = rectBounds(stage);
    
    const outerLeft = stageBounds.left;
    const outerRight = stageBounds.right;
    const outerTop = stageBounds.top;
    const outerBottom = stageBounds.bottom;

    // limit to stage bounds
    const target = vectorLimit({
        x: origin.x,
        y: origin.y
    }, stage);

    let left = sign.x > 0 ? anchor.x : Math.min(target.x, anchor.x);
    let right = sign.x > 0 ? Math.max(anchor.x, target.x) : anchor.x;
    let top = sign.y > 0 ? anchor.y : Math.min(target.y, anchor.y);
    let bottom = sign.y > 0 ? Math.max(anchor.y, target.y) : anchor.y;

    if (cropAspectRatio) {

        const dx = target.x - anchor.x;

        if (sign.x > 0) {
            right = Math.max(anchor.x, anchor.x + (sign.x * dx));
        }
        else {
            left = Math.min(anchor.x, anchor.x - (sign.x * dx));
        }

        if (sign.y > 0) {
            bottom = Math.max(anchor.y, anchor.y + (sign.x * dx * cropAspectRatio));
        }
        else {
            top = Math.min(anchor.y, anchor.y - (sign.x * dx * cropAspectRatio));
        }

    }

    let free = rectFromBounds({top, right, bottom, left});
    let limited = rectFromBounds({top, right, bottom, left});

    if (minSize.width && minSize.height) {

        let minWidth = minSize.width;
        let minHeight = minSize.height;

        if (cropAspectRatio) {
            if (cropAspectRatio === 1) { // square
                minWidth = Math.max(minWidth, minHeight);
                minHeight = minWidth;
            }
            else { // landscape
                if (minWidth < minHeight) {
                    minWidth = minHeight / cropAspectRatio;
                }
                else if (minWidth > minHeight) {
                    minHeight = minWidth * cropAspectRatio;
                }
                else {
                    minWidth = minHeight / cropAspectRatio;
                }
            }
        }

        if (right - left < minWidth) {
            if (sign.x > 0) {
                right = anchor.x + minWidth;
            }
            else {
                left = anchor.x - minWidth;
            }
        }

        if (bottom - top < minHeight) {
            if (sign.y > 0) {
                bottom = anchor.y + minHeight;
            }
            else {
                top = anchor.y - minHeight;
            }
        }

        let maxWidth = maxSize.width;
        let maxHeight = maxSize.height;
        if (cropAspectRatio) {
            if (cropAspectRatio < 1) {
                maxWidth = maxHeight / cropAspectRatio;
            }
            else {
                maxHeight = maxWidth * cropAspectRatio;
            }
        }

        if (right - left > maxWidth) {
            if (sign.x < 0) {
                left = anchor.x - maxWidth;
            }
            else {
                right = anchor.x + maxWidth;
            }
        }
        
        if (bottom - top > maxHeight) {
            if (sign.y < 0) {
                top = anchor.y - maxHeight;
            }
            else {
                bottom = anchor.y + maxHeight;
            }
        }
        
    }


    // ------------------------------------------
    // make sure the rectangle is not zero sized
    if (right - left === 0) {
        if (sign.x > 0) {
            right = anchor.x + 2;
        }
        else {
            left = anchor.x - 2;
        }
    }

    if (bottom - top === 0) {
        if (sign.y > 0) {
            bottom = anchor.y + 2;
        }
        else {
            top = anchor.y - 2;
        }
    }

    // ------------------------------------------
    // make sure the rectangle didn't fall out of view bounds
    if (Math.round(left) < outerLeft || Math.round(right) > outerRight || Math.round(top) < outerTop || Math.round(bottom) > outerBottom) {
        
        const maxHeight = outerBottom - outerTop;
        const maxWidth = outerRight - outerLeft;

        if (left < outerLeft) {
            left = outerLeft;
            const width = Math.min(right - left, maxWidth);
            right = left + width;
            if (cropAspectRatio) {
                if (sign.y > 0) {
                    bottom = anchor.y + (width * cropAspectRatio);
                }
                if (sign.y < 0) {
                    top = anchor.y - (width * cropAspectRatio);
                }
            }
        }
    
        if (right > outerRight) {
            right = outerRight;
            const width = Math.min(right - left, maxWidth);
            left = right - width;
            if (cropAspectRatio) {
                if (sign.y > 0) {
                    bottom = anchor.y + (width * cropAspectRatio);
                }
                if (sign.y < 0) {
                    top = anchor.y - (width * cropAspectRatio);
                }
            }
        }
    
        if (top < outerTop) {
            top = outerTop;
            const height = Math.min(bottom - top, maxHeight);
            bottom = top + height;
            if (cropAspectRatio) {
                if (sign.x > 0) {
                    right = anchor.x + (height / cropAspectRatio);
                }
                if (sign.x < 0) {
                    left = anchor.x - (height / cropAspectRatio);
                }
            }
        }
    
        if (bottom > outerBottom) {
            bottom = outerBottom;
            const height = Math.min(bottom - top, maxHeight);
            top = bottom - height;
            if (cropAspectRatio) {
                if (sign.x > 0) {
                    right = anchor.x + (height / cropAspectRatio);
                }
                if (sign.x < 0) {
                    left = anchor.x - (height / cropAspectRatio);
                }
            }
        }
        
        free = rectFromBounds({top, right, bottom, left});
    }

    limited = rectFromBounds({top, right, bottom, left});

    return {
        free,
        limited
    }

};

const getTargetRect = (crop, targetAspectRatio, minSize) => {

    // modify crop
    const targetCrop = rectClone(crop);

    // turn into square
    targetCrop.width = Math.min(targetCrop.height, targetCrop.width);//targetCrop.height;//
    targetCrop.height = targetCrop.width;

    // apply new aspect ratio
    targetCrop.height = targetCrop.width * targetAspectRatio;
    
    if (targetCrop.height < minSize.height) {
        targetCrop.height = minSize.height;
        targetCrop.width = targetCrop.height / targetAspectRatio;
    }

    if (targetCrop.width < minSize.width) {
        targetCrop.width = minSize.width;
        targetCrop.height = targetCrop.width * targetAspectRatio;
    }

    return targetCrop;
};

const TURN = Math.PI / 2;
const PI_QUARTER = Math.PI / 4;

const splitRotation = (rotation) => {

    let quarter = roundFloat(PI_QUARTER);
    let half = roundFloat(TURN);

    let div = rotation / half;
    let main = Math.floor(div) * half;
    let sub = rotation - main;
    
    if (sub > quarter) {
        sub = sub - half;
        main += half;
    }
    
    const result = {
        main,
        sub
    };

    return result;
};

const getImageSize = (file) => new Promise((resolve, reject) => {

    const image = new Image();
    image.src = URL.createObjectURL(file);
    image.onerror = err => {
        clearInterval(intervalId);
        reject(err);
    };
    const intervalId = setInterval(() => {
        if (image.naturalWidth && image.naturalHeight) {
            clearInterval(intervalId);
            URL.revokeObjectURL(image.src);
            resolve({
                width: image.naturalWidth,
                height: image.naturalHeight
            });
        }
    }, 1);

});

const scaleImageSize = (size, maxSize) => {

    const result = {
        width: size.width,
        height: size.height
    };

    if (size.width > maxSize.width || size.height > maxSize.height) {

        const aspectRatio = size.height / size.width;
        const scalarX = maxSize.width / size.width;
        const scalarY = maxSize.height / size.height;

        if (scalarX < scalarY) {
            result.width = size.width * scalarX;
            result.height = result.width * aspectRatio;
        }
        else {
            result.height = size.height * scalarY;
            result.width = result.height / aspectRatio;
        }
    }
    
    return result;
};

const leftPad = (value, padding = '') =>
    (padding + value).slice(-padding.length);

const getDateString = (date = new Date()) =>
    `${date.getFullYear()}-${leftPad(date.getMonth() + 1, '00')}-${leftPad(
        date.getDate(),
        '00'
    )}_${leftPad(date.getHours(), '00')}-${leftPad(
        date.getMinutes(),
        '00'
    )}-${leftPad(date.getSeconds(), '00')}`;

const getBaseCropInstructions = (query, state, options = {}, size = {}) => {

    const selectedCropAspectRatio = query('GET_CROP_ASPECT_RATIO');
    
    // the base crop instrucitons for an image
    const base = {
        center: {
            x: .5,
            y: .5
        },
        flip: {
            horizontal: false,
            vertical: false
        },
        zoom: 1,
        rotation: 0,
        aspectRatio: null
    };

    // if options are not supplied we will use the `crop` property, if set
    if (options) {
        Object.assign(base, options);
    }
    else {
        if (state.options.crop) {
            Object.assign(base, state.options.crop);
        }
        else {
            // if no options supplied, the cropAspectRatio property is used as default (if set)
            base.aspectRatio = selectedCropAspectRatio;
        }
    }

    // if output size has been defined override aspect ratio
    const { width, height } = size;
    if (width && height) {
        base.aspectRatio = height / width;
    }
    else if (state.instructions.size) {
        const { width, height } = state.instructions.size;
        if (width && height) {
            base.aspectRatio = height / width;
        }
    }

    return base;
};

const capitalizeFirstLetter = str => str.charAt(0).toUpperCase() + str.slice(1);

const getExtensionFromFilename = name => name.split('.').pop();

const guesstimateExtension = type => {
    // if no extension supplied, exit here
    if (typeof type !== 'string') {
        return '';
    }

    // get subtype
    const subtype = type.split('/').pop();

    // is svg subtype
    if (/svg/.test(subtype)) {
        return 'svg';
    }

    if (/zip|compressed/.test(subtype)) {
        return 'zip';
    }

    if (/plain/.test(subtype)) {
        return 'txt';
    }

    if (/msword/.test(subtype)) {
        return 'doc';
    }

    // if is valid subtype
    if (/[a-z]+/.test(subtype)) {
        // always use jpg extension
        if (subtype === 'jpeg') {
            return 'jpg';
        }

        // return subtype
        return subtype;
    }

    return '';
};

const getFileFromBlob = (
    blob,
    filename,
    type = null,
    extension = null
) => {
    const file =
        typeof type === 'string'
            ? blob.slice(0, blob.size, type)
            : blob.slice(0, blob.size, blob.type);
    file.lastModifiedDate = new Date();

    // if blob has name property, use as filename if no filename supplied
    if (!isString(filename)) {
        filename = getDateString();
    }

    // if filename supplied but no extension and filename has extension
    if (filename && extension === null && getExtensionFromFilename(filename)) {
        file.name = filename;
    }
    else {
        extension = extension || guesstimateExtension(file.type);
        file.name = filename + (extension ? '.' + extension : '');
    }

    return file;
};

const getFilenameWithoutExtension = name =>
    name.substr(0, name.lastIndexOf('.')) || name;

// only handles image/jpg, image/jpeg, image/png, and image/svg+xml for now
const ExtensionMap = {
    'jpeg': 'jpg',
    'svg+xml': 'svg'
};

const renameFileToMatchMimeType = (filename, mimeType) => {
    const name = getFilenameWithoutExtension(filename);
    const type = mimeType.split('/')[1];
    const extension = ExtensionMap[type] || type;
    return `${name}.${extension}`;
};

// returns all the valid output formats we can encode towards
const getValidOutputMimeType = type => /jpeg|png|svg\+xml/.test(type) ? type : 'image/jpeg';

const isColorMatrix = v => Array.isArray(v) && v.length === 20;

//#if !_EXCLUDE_MARKUP_UTIL

const MARKUP_RECT = [
    'x',
    'y',
    'left',
    'top',
    'right',
    'bottom',
    'width',
    'height'
];

const toOptionalFraction = value => typeof value === 'string' && /%/.test(value) ? parseFloat(value) / 100 : value;

const getUniqueId$2 = () =>
    Math.random()
        .toString(36)
        .substr(2, 9);


// adds default markup properties, clones markup
const prepareMarkup = markup => {

    const [type, props] = markup;

    let allowSelect = props.allowSelect !== false;
    let allowMove = props.allowMove !== false;
    let allowResize = props.allowResize !== false;
    const allowInput = props.allowInput !== false;
    const allowDestroy = props.allowDestroy !== false;
    const allowEdit = typeof props.allowEdit === 'undefined' ? true : props.allowEdit;

    if (props.allowResize === true || props.allowMove === true || props.allowInput === true || props.allowEdit) {
        allowSelect = true;
    }

    if (props.allowMove === false) {
        allowResize = false;
    }
    
    if (props.allowResize === true) {
        allowMove = true;
    }

    const rect = props.points ? {} : MARKUP_RECT.reduce((prev, curr) => {
        prev[curr] = toOptionalFraction(props[curr]);
        return prev;
    }, {});

    if (props.points) {
        allowResize = false;
    }
    
    return [
        type,
        {
            zIndex: 0,
            id: getUniqueId$2(),
            ...props,
            ...rect,
            isDestroyed: false,
            isSelected: false,
            isDirty: true,
            allowDestroy,
            allowSelect,
            allowMove,
            allowResize,
            allowInput,
            allowEdit
        }
    ]
};

//#endif

const getFilenameFromHeader = (header) => {
    if (!header) return null;
    const matches = header.split(/filename=|filename\*=.+''/)
        .splice(1)
        .map(name => name.trim().replace(/^["']|[;"']{0,2}$/g, ''))
        .filter(name => name.length);
    
    return matches.length ? decodeURI(matches[matches.length-1]) : null;
};

//#if !_EXCLUDE_COLOR_UTIL
// -1 -> 1
const brightness = v => [
    1, 0, 0, 0, v,
    0, 1, 0, 0, v,
    0, 0, 1, 0, v,
    0, 0, 0, 1, 0
];
//#endif

//#if !_EXCLUDE_COLOR_UTIL
// 1 -> 2
const contrast = v => [
    v, 0, 0, 0, .5 * (1 - v),
    0, v, 0, 0, .5 * (1 - v),
    0, 0, v, 0, .5 * (1 - v),
    0, 0, 0, 1, 0
];
//#endif

//#if !_EXCLUDE_COLOR_UTIL
const saturation = (v = 0) => [
    .213 + 0.787 * v, .715 - .715 * v, .072 - .072 * v, 0, 0,
    .213 - 0.213 * v, .715 + .285 * v, .072 - .072 * v, 0, 0,
    .213 - 0.213 * v, .715 - .715 * v, .072 + .928 * v, 0, 0,
                   0,               0,               0, 1, 0
];
//#endif

//#if !_EXCLUDE_COLOR_UTIL
// 0 -> 1
const exposure = v => [
    v, 0, 0, 0, 0,
    0, v, 0, 0, 0,
    0, 0, v, 0, 0,
    0, 0, 0, 1, 0
];
//#endif

// 2x1 pixel image 90CW rotated with orientation header
const testSrc = 'data:image/jpg;base64,/9j/4AAQSkZJRgABAQEASABIAAD/4QA6RXhpZgAATU0AKgAAAAgAAwESAAMAAAABAAYAAAEoAAMAAAABAAIAAAITAAMAAAABAAEAAAAAAAD/2wBDAP//////////////////////////////////////////////////////////////////////////////////////wAALCAABAAIBASIA/8QAJgABAAAAAAAAAAAAAAAAAAAAAxABAAAAAAAAAAAAAAAAAAAAAP/aAAgBAQAAPwBH/9k=';

// should correct orientation if is presented in landscape, in which case the browser doesn't autocorrect
let shouldCorrect = undefined;
let testImage = isBrowser() ? new Image() : {};
testImage.onload = () => {
    shouldCorrect = testImage.naturalWidth > testImage.naturalHeight;
    testImage = undefined;
};
testImage.src = testSrc;

const shouldCorrectImageExifOrientation = () => shouldCorrect;

let testResult$1 = null;
const isIE = () => {
    if (testResult$1 === null) {
        testResult$1 = /MSIE|Trident/.test(window.navigator.userAgent);
    }
    return testResult$1;
};

//#if !_EXCLUDE_COLOR_UTIL
const COLOR_TOOLS = {
    contrast,
    exposure,
    brightness,
    saturation
};
//#endif


//#if !_EXCLUDE_MARKUP_UTIL
const getColorProperty = (settings) => {
    if (settings.borderWidth) {
        return 'borderColor';
    }
    if (settings.lineWidth) {
        return 'lineColor';
    }
    if (settings.fontColor) {
        return 'fontColor';
    }
    if (settings.backgroundColor) {
        return 'backgroundColor';
    }
};

const getColor = (settings) => {
    const { fontColor, backgroundColor, lineColor, borderColor } = settings;
    return fontColor || backgroundColor || lineColor || borderColor;
};
//#endif

// the amount to turn each rotate click
const TURN$1 = Math.PI / 2;


const getOutputSize = (query) => {

    // automatic output size
    const size = {
        upscale: query('GET_OUTPUT_UPSCALE'),
        mode: query('GET_OUTPUT_FIT'),
        width: query('GET_OUTPUT_WIDTH'),
        height: query('GET_OUTPUT_HEIGHT')
    };

    // override with user defined size if set
    //#if !_EXCLUDE_RESIZE_UTIL
    const userDefinedSize = query('GET_SIZE_INPUT');

    if ((userDefinedSize.width || userDefinedSize.height)) {

        let width = userDefinedSize.width;
        let height = userDefinedSize.height;
        const aspectRatio = query('GET_CROP_RECTANGLE_ASPECT_RATIO');

        if (width && !height) {
            height = width / aspectRatio;
        }
        else if (height && !width) {
            width = height * aspectRatio;
        }
        
        // set new width and height targets
        size.width = width;
        size.height = height;

        // always upscale to user defined size
        size.upscale = true;

        // and force the required
        size.mode = 'force';
    }
    //#endif

    return size;
};

const getPreparedImageSize = (outputSize, query) => {

    const uid = query('GET_UID');

    const view = query('GET_CROP', uid, Date.now());

    const imageData = {
        width: view.cropStatus.currentWidth,
        height: view.cropStatus.currentHeight
    };

    let { mode, width, height, upscale } = outputSize;
    
    // if no resize data supplied exit here
    if (!width && !height) return imageData;
    
    if (width === null) {
        width = height;
    }
    else if (height === null) {
        height = width;
    }
    
    if (mode !== 'force') {

        let scalarWidth = width / imageData.width;
        let scalarHeight = height / imageData.height;
        let scalar = 1;
        
        if (mode === 'cover') {
            scalar = Math.max(scalarWidth, scalarHeight);
        }
        else if (mode === 'contain') {
            scalar = Math.min(scalarWidth, scalarHeight);
        }
    
        // if image is too small, exit here with original image
        if (scalar > 1 && upscale === false) return imageData;
        
        width = imageData.width * scalar;
        height = imageData.height * scalar;
    }

    return {
        width: Math.round(width),
        height: Math.round(height)
    }
};

const getActiveMarkupFromState = (state) => state.markup.filter(m => !m[1].isDestroyed);

const cleanMarkupForExport = (markup) => markup.map(item => {

    const data = {
        ...item[1]
    };

    // clean up empty props
    Object.keys(data).forEach(key => {
        if (typeof data[key] === 'undefined') {
            delete data[key];
        }
    });

    // remove bools
    delete data.isDestroyed;
    delete data.isSelected;
    delete data.isDirty;

    return [item[0], data];
});

const prepareOutput = (options, state, query) => new Promise((resolve, reject) => {

    // output
    const output = {
        data: null,
        file: null
    };

    const crop = getCropFromStateRounded(state.image, state.crop);
    
    const size = getOutputSize(query);

    // create the new crop outline
    const data = {
        crop,
        image: {
            ...getPreparedImageSize(size, query),
            orientation: state.file.orientation,
        },
        size,
        output: {
            type: query('GET_OUTPUT_TYPE'),
            quality: query('GET_OUTPUT_QUALITY'),
            background: state.options.outputCanvasBackgroundColor
        },
        //#if !_EXCLUDE_FILTER_UTIL
        filter: state.colorMatrices['filter'] ? {
            id: state.filterName,
            value: state.filterValue,
            matrix: state.colorMatrices['filter']
        } : null,
        //#endif
        //#if !_EXCLUDE_COLOR_UTIL
        color: Object.keys(state.colorValues).length ? Object.keys(state.colorValues).reduce((obj, key) => {
            obj[key] = {
                value: state.colorValues[key],
                matrix: state.colorMatrices[key].map(v => roundFloat(v, 5))
            };
            return obj;
        }, {}) : null,
        //#endif
        markup: cleanMarkupForExport(getActiveMarkupFromState(state).map(m => [m[0], {...m[1]}])),
        colorMatrix: query('GET_COLOR_MATRIX')
    };

    if (options.data) {
        output.data = data;
    }

    //#if !_EXCLUDE_TRANSFORM_IMAGE
    if (options.file) {
        const config = {
            beforeCreateBlob: query('GET_BEFORE_CREATE_BLOB'),
            afterCreateBlob: query('GET_AFTER_CREATE_BLOB'),
            stripImageHead: query('GET_OUTPUT_STRIP_IMAGE_HEAD'),
            canvasMemoryLimit: query('GET_OUTPUT_CANVAS_MEMORY_LIMIT')
        };

        const file = state.file.data;

        const transforms = {
            ...data,
            filter: data.colorMatrix,
            markup: data.markup
        };

        transformImage(file, transforms, config)
            .then(blob => {

                // set file object
                output.file = getFileFromBlob(

                    // output data
                    blob,

                    // rename the original filename to match the mime type of the output image
                    renameFileToMatchMimeType(file.name, getValidOutputMimeType(blob.type))
                );

                // done!
                resolve(output);
            })
            .catch(reject);
    }
    else {
        //#endif
        resolve(output);
        //#if !_EXCLUDE_TRANSFORM_IMAGE
    }
    //#endif

});

const resetRotationScale = (state) => {
    state.crop.draft.rotateMinScale = null;
};

const storeRotationScale = (state) => {
    if (!state.crop.draft.rotateMinScale) {
        state.crop.draft.rotateMinScale = state.crop.transforms.scale;
    }
};

const rotate = (state, rotation, minSize, turn = false, matchImageBounds = true) => {

    // this makes sure that while we rotate, even when confirming a rotation, 
    // the rotated image adheres to the initial scale
    storeRotationScale(state);

    const transforms = {
        ...state.crop.transforms,
        scale: state.crop.draft.rotateMinScale
    };

    state.crop.draft.transforms = getRotateTransforms(
        state.image,
        state.crop.rectangle, 
        transforms, 
        rotation.main + rotation.sub,
        minSize,
        state.crop.draft.transforms ? state.crop.draft.transforms.rotation : state.crop.rotation.main + state.crop.rotation.sub,
        turn,
        matchImageBounds
    );
    
    state.crop.rotation = splitRotation(state.crop.draft.transforms.rotation);
};

const resetCrop = (state, query) => {

    if (state.stage === null) return;

    // set crop limit
    const shouldScaleToFit = typeof state.instructions.crop.scaleToFit === 'undefined'
        ? typeof state.crop.limitToImageBounds === 'undefined'
            ? state.options.cropLimitToImageBounds
            : state.crop.limitToImageBounds
        : state.instructions.crop.scaleToFit;
    
    const stageRect = query('GET_STAGE_RECT', state.instructions.crop);

    // reset crop
    state.crop.rectangle = getCenteredCropRect(stageRect.fits ? stageRect : state.stage, state.instructions.crop.aspectRatio || state.image.aspectRatio);
    state.crop.draft.rectangle = null;

    // if only match initial we need to move the crop to the center of the view
    if (stageRect.mode !== 'stage' && stageRect.fits) {
        state.crop.rectangle.x = stageRect.x;
        state.crop.rectangle.y = stageRect.y;
    }
    
    state.crop.transforms = getImageTransformsFromCrop(
        state.instructions.crop, 
        stageRect, 
        state.image, 
        shouldScaleToFit
    );
    state.crop.draft.transforms = null;

    state.crop.rotation = splitRotation(state.instructions.crop.rotation);

    state.crop.flip = {...state.instructions.crop.flip};

    // if instructions contains a crop aspect ratio that is matched in the crop aspect ratio options list, use that, else fallback to the forced version
    const aspectRatioOptions = query('GET_CROP_ASPECT_RATIO_OPTIONS') || [];
    const matchedAspectRatioOption = aspectRatioOptions.map(option => option.value.aspectRatio).find(aspectRatio => aspectRatio === state.instructions.crop.aspectRatio);
    const hasFreeAspectRatioOption = aspectRatioOptions.find(option => option.value.aspectRatio === null);
    
    // use the matched crop aspect ratio || if a crop aspect ratio was set, always use that as a starting point
    if (matchedAspectRatioOption) {
        state.crop.aspectRatio = matchedAspectRatioOption;
    }
    else if (hasFreeAspectRatioOption && aspectRatioOptions.length) {
        state.crop.aspectRatio = null;
    }
    else {
        state.crop.aspectRatio = query('GET_CROP_ASPECT_RATIO');
    }
    
    state.crop.isDirty = false;
};

const reset = (state, query, dispatch) => {

    if (state.stage === null) return;

    clearCenterTimeout(state);

    //#if !_EXCLUDE_RESIZE_UTIL
    // reset size
    state.size.width = state.instructions.size ? state.instructions.size.width : false;
    state.size.height = state.instructions.size ? state.instructions.size.height : false;
    state.size.aspectRatioLocked = true;
    state.size.aspectRatioPrevious = false;
    //#endif

    // set crop limit
    const shouldScaleToFit = typeof state.instructions.crop.scaleToFit === 'undefined'
        ? typeof state.crop.limitToImageBounds === 'undefined'
            ? state.options.cropLimitToImageBounds
            : state.crop.limitToImageBounds
        : state.instructions.crop.scaleToFit;
    
    // reset the crop rectangle
    resetCrop(state, query);
    

    //#if !_EXCLUDE_MARKUP_UTIL
    // reset markup
    if (state.instructions.markup) {
        dispatch('MARKUP_SET_VALUE', {
            value: state.instructions.markup
        });
    }
    //#endif

    //#if !_EXCLUDE_CROP_UTIL
    dispatch('CROP_SET_LIMIT', {
        value: shouldScaleToFit, 
        silent: true
    });
    //#endif

    //#if !_EXCLUDE_COLOR_UTIL
    // reset colors
    Object.keys(state.instructions.color).forEach(tool => dispatch('COLOR_SET_VALUE', { key: tool, value: state.instructions.color[tool] }));
    //#endif

    //#if !_EXCLUDE_FILTER_UTIL
    // reset filter
    dispatch('FILTER_SET_VALUE', { value: state.instructions.filter });
    //#endif
    
    resetRotationScale(state);
};



const recenter = (state, query) => {

    if (!state.stage) return;

    clearCenterTimeout(state);
    
    const currentCropRect = state.crop.rectangle;
    const currentCropAspectRatio = currentCropRect.height / currentCropRect.width;
    const targetCropAspectRatio = state.crop.aspectRatio;

    if (targetCropAspectRatio !== null && roundFloat(currentCropAspectRatio, 3) !== roundFloat(targetCropAspectRatio, 3)) {
        
        const minCropSize = query('GET_MIN_CROP_SIZE');

        minCropSize.width = roundFloat(minCropSize.width);
        minCropSize.height = roundFloat(minCropSize.height);

        const edgeMin = Math.min(currentCropRect.width, currentCropRect.height);
        const resultMin = Math.min(edgeMin * targetCropAspectRatio, edgeMin / targetCropAspectRatio);
        const sizeMax = Math.max(minCropSize.width, minCropSize.height);

        if (resultMin < sizeMax) {
            state.crop.rectangle = getTargetRect({ ...state.crop.rectangle }, targetCropAspectRatio, minCropSize);
            state.crop.draft.transforms = getImageTransformsFromRect(state.image, state.crop.rectangle, state.crop.transforms);
        }
    }

    const transforms = state.crop.draft.transforms || state.crop.transforms;
    const crop = getCropFromView(state.image, state.crop.rectangle, transforms, state.crop.limitToImageBounds);
    
    // if aspect ratio has a value, use that
    if (state.crop.aspectRatio) {
        crop.aspectRatio = state.crop.aspectRatio;
    }

    // get the stage rectangle to use
    const stageRect = query('GET_STAGE_RECT', crop);
    state.crop.transforms = getImageTransformsFromCrop(
        crop, 
        stageRect,
        state.image,
        crop.scaleToFit
    );
    state.crop.draft.transforms = null;
    
    // use fixed aspect ratio or aspect ratio of rectangle
    const cropAspectRatio = state.crop.aspectRatio || state.crop.rectangle.height / state.crop.rectangle.width;
    state.crop.rectangle = getCenteredCropRect(stageRect, cropAspectRatio);
    state.crop.draft.rectangle = null;

    // if only match initial we need to move the crop to the center of the view
    if (stageRect.mode !== 'stage') {
        state.crop.rectangle.x += stageRect.x;
        state.crop.rectangle.y += stageRect.y;
    }

    resetRotationScale(state);
};

const startCenterTimeout = (state, query, dispatch) => {

    const timeout = query('GET_CROP_ZOOM_TIMEOUT');
    if (!timeout) return;

    clearTimeout(state.zoomTimeoutId);
    state.zoomTimeoutId = setTimeout(() => {
        dispatch('CROP_ZOOM');
    }, timeout);

};

const resetCenterTimeout = (state, query, dispatch) => {
    clearCenterTimeout(state);
    startCenterTimeout(state, query, dispatch);
};

const clearCenterTimeout = (state) => {
    clearTimeout(state.zoomTimeoutId);
};

const confirmCropDraft = (state) => {

    state.crop.rectangle = state.crop.draft.rectangle.limited;
    state.crop.draft.rectangle = null;

    confirmImageDraft(state);

    resetRotationScale(state);
};

const copyConfirmed = (state) => {
    state.crop.draft.transforms = copyImageTransforms(state.crop.transforms);
    state.crop.draft.rectangle = {
        limited: rectClone(state.crop.rectangle),
        free: rectClone(state.crop.rectangle)
    };
    clearCenterTimeout(state);
};

const getMinScale = (rectangle, minSize) => Math.min(rectangle.width / minSize.width, rectangle.height / minSize.height);

const getRotateTransforms = (image, rectangle, transforms, value, minSize, currentRotation, turn, limitToImageBounds) => {

    const intendedTransforms = {
        ...copyImageTransforms(transforms),
        rotation: value
    };
    
    const imageTransforms = limitToImageBounds ? limitImageTransformsToCropRect(
        image,
        rectangle,
        intendedTransforms,
        'rotating'
    ) : intendedTransforms;

    const minScale = getMinScale(rectangle, minSize);
    
    if (roundFloat(imageTransforms.scale, 5) > roundFloat(minScale, 5)) {
        if (turn) {
            currentRotation += turn * 2;
        }
        return {
            ...copyImageTransforms(transforms),
            rotation: currentRotation,
            interaction: {
                rotation: imageTransforms.rotation
            }
        }
    }

    imageTransforms.scale = Math.min(minScale, imageTransforms.scale);
    imageTransforms.interaction = { rotation: imageTransforms.rotation };

    return imageTransforms;
};

const getResizeTransforms = (image, rectangle, transforms, value, minSize, limitToImageBounds) => {
    
    const scale = Math.max(0.0000000001, value);

    const intendedTransforms = {
        ...copyImageTransforms(transforms),
        scale
    };

    const imageTransforms = limitToImageBounds ? limitImageTransformsToCropRect(
        image,
        rectangle,
        intendedTransforms,
        'resizing'
    ) : intendedTransforms;
    
    const minScale = getMinScale(rectangle, minSize);

    imageTransforms.scale = Math.min(minScale, imageTransforms.scale);
    imageTransforms.interaction = { scale };

    return imageTransforms;
};

const getTranslateTransforms = (image, rectangle, transforms, offset, limitToImageBounds) => {

    const translation = {
        x: transforms.translation.x + offset.x,
        y: transforms.translation.y + offset.y
    };

    const intendedTransforms = {
        ...copyImageTransforms(transforms),
        translation
    };

    const imageTransforms = limitToImageBounds ? limitImageTransformsToCropRect(
        image,
        rectangle,
        intendedTransforms,
        'moving'
    ) : intendedTransforms;

    imageTransforms.interaction = { translation };

    return imageTransforms;
};

const correctCropRectangleByResize = (state, query) => {

    const currentScale = roundFloat(state.crop.draft.transforms.scale, 5);
    const intendedScale = roundFloat(state.crop.draft.targetSize, 5);

    // if not zooming, no need to correct, exit
    const isZoomingIn = intendedScale < currentScale;    
    if (!isZoomingIn) return false;

    // if forcing crop aspect ratio, exit
    if (state.crop.aspectRatio !== null) return false;

    // if not limiting crop to image, exit
    if (state.crop.limitToImageBounds === false) return false;

    // if slightly rotated, no need to correct,  exit
    if (roundFloat(state.crop.rotation.sub, 5) !== 0) return false;

    const isRotated = !(roundFloat(state.crop.rotation.main / TURN$1, 5) % 2 === 0);
    const imagePreviewAspectRatio = isRotated ? state.image.width / state.image.height : state.image.height / state.image.width;
    const cropRectangleAspectRatio = state.crop.rectangle.height / state.crop.rectangle.width;
    
    // if aspect ratio of image and crop rectangle match, no need to correct, exit
    if (imagePreviewAspectRatio === cropRectangleAspectRatio) return false;
    
    // check if is centered already, if not, exit
    const stageCenterX = state.stage.x + (state.stage.width * .5);
    const stageCenterY = state.stage.y + (state.stage.height * .5);
    const cropCenterX = state.crop.rectangle.x + (state.crop.rectangle.width * .5);
    const cropCenterY = state.crop.rectangle.y + (state.crop.rectangle.height * .5);
    
    // is centered?
    if (cropCenterX !== stageCenterX || cropCenterY !== stageCenterY) return false;
    
    // scale crop rectangle to fit image aspect ratio
    const stageRect = query('GET_STAGE_RECT');
    state.crop.rectangle = getCenteredCropRect(stageRect, imagePreviewAspectRatio);
    
    if (stageRect.mode !== 'stage') {
        state.crop.rectangle.x += stageRect.x;
        state.crop.rectangle.y += stageRect.y;
    }
    
    state.crop.transforms = getImageTransformsFromCrop(
        {
            center: {
                x: .5,
                y: .5
            },
            rotation: state.crop.transforms.rotation,
            zoom: 1,
            aspectRatio: imagePreviewAspectRatio
        }, 
        stageRect, 
        state.image, 
        true
    );
    state.crop.draft.transforms = null;

    return true;
};

const confirmImageDraft = (state) => {

    // clear crop draft to be sure
    state.crop.draft.rectangle = null;

    // transfer draft transforms
    state.crop.transforms = state.crop.draft.transforms || state.crop.transforms;
    state.crop.transforms.interaction = null;
    state.crop.draft.transforms = null;

    // set new transform origin
    state.crop.transforms = {
        ...state.crop.transforms,
        ...getTransformOrigin(state.image, state.crop.rectangle, state.crop.transforms)
    };
    
    state.crop.isRotating = false;
    state.crop.isDirty = true;
};

const getResponseHeaderSilent = (xhr, header) => {
    const headers = xhr.getAllResponseHeaders();
    if (headers.indexOf(header) >= 0) {
        return xhr.getResponseHeader(header);
    }
    return null;
};

const loadImageFromURL = (url, options = {}) => {
    const {
        progress = (progress) => {},
        load = (file, instructions) => {},
        error = () => {}
    } = options;

    const xhr = new XMLHttpRequest();
    xhr.onprogress = e => progress(e.lengthComputable ? e.loaded / e.total : null);
    xhr.onerror = () => error(xhr),
    xhr.onload = () => {

        const blob = xhr.response;
        if (!blob) return error(xhr);

        // we store the response mime type so we can add it to the blob later on, if it's missing (happens on Safari 10)
        const mimetype = xhr.getResponseHeader('Content-Type');

        // try to get filename and any file instructions as well
        const filenameHeader = getResponseHeaderSilent(xhr, 'Content-Disposition');
        const filename = filenameHeader ? getFilenameFromHeader(filenameHeader) : null;
        
        // try to extract any doka instructions
        const instructionsHeader = getResponseHeaderSilent(xhr, 'Content-Doka');
        let instructions = null;
        if (instructionsHeader) {
            try { instructions = JSON.parse(instructionsHeader); } catch(err) {}
        }
        
        // set blob type
        if (!blob.type.length && mimetype) {
            blob = blob.slice(0, blob.size, mimetype);
        }

        // set name of blob
        if (filename) {
            blob.name = filename;
        }

        load(blob, instructions);
    };
    xhr.open('GET', url);
    xhr.responseType = 'blob';
    xhr.send();
};

const dataURIToBlob = dataURI => {
    const parts = dataURI.split(',');
    const type = parts[0].match(/([a-z]+\/[a-z]+)/)[0];
    const byteString = atob(parts[1]);
    const l = byteString.length;
    const ab = new ArrayBuffer(byteString.length);
    const ia = new Uint8Array(ab);
    for (var i=0; i<l; i++) {
        ia[i] = byteString.charCodeAt(i);
    }
    return new Blob([ab], { type: type });
};

const isDataURI = str => /^data:/.test(str);

const loadImage$1 = (src, { progress }) => new Promise((resolve, reject) => {

    if (isString(src)) {

        const srcIsDataURI = isDataURI(src);

        if (srcIsDataURI && isIE()) {
            return resolve({
                file: dataURIToBlob(src)
            });
        }

        loadImageFromURL(src, {
            progress: srcIsDataURI ? () => {} : progress,
            error: reject,
            load: (blob, instructions) => resolve({
                file: blob,
                fileInstructions: instructions
            })
        });
        return;
    }

    if (src instanceof Blob) {
        resolve({ file: src });
        return;
    }

    if (src.nodeName === 'IMG') {
        const imageToBlob = (image) => {
            const canvas = document.createElement('canvas');
            canvas.width = image.naturalWidth;
            canvas.height = image.naturalHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(image, 0, 0);
            canvas.toBlob(blob => resolve({ file: blob }));
        };
        if (src.complete) {
            imageToBlob(src);
            return;
        }
        src.onload = () => imageToBlob(src);
        return;
    }

    if (src.nodeName === 'CANVAS') {
        src.toBlob(blob => resolve({ file: blob }));
        return;
    }

    reject(src);
});

const shouldAbortImageLoad = (state) => state.file === false;

const actions = (dispatch, query, state) => ({

    SET_UID: ({ id }) => {
        state.uid = id;
    },

    // open the editor
    AWAIT_IMAGE: () => {
        if (state.file) return;
        state.noImageTimeout = setTimeout(() => {
            dispatch('AWAITING_IMAGE');
        }, 250);
    },

    REQUEST_REMOVE_IMAGE: () => {
        dispatch('UNLOAD_IMAGE');
        state.file = false;
        state.noImageTimeout = setTimeout(() => {
            dispatch('AWAITING_IMAGE');
        }, 500);
    },

    DID_UNLOAD_IMAGE: () => {
        dispatch('ABORT_IMAGE');
    },

    REQUEST_ABORT_IMAGE: (data) => {
        dispatch('UNLOAD_IMAGE');
        state.file = false;
        state.queuedFile = data;
    },

    DID_SET_SRC: (action) => {
        if (action.value === action.prevValue) return;
        clearTimeout(state.noImageTimeout);
        dispatch('REQUEST_LOAD_IMAGE', { source: action.value });
    },

    ABORT_IMAGE: () => {
        state.file = null;
        if (!state.queuedFile) return;
        const image = state.queuedFile;
        state.queuedFile = null;
        dispatch('REQUEST_LOAD_IMAGE', image);
    },

    REQUEST_LOAD_IMAGE: ({ source, success = () => {}, failure = (err) => {}, options, resolveOnConfirm = false }) => {
        
        clearTimeout(state.noImageTimeout);

        // no file supplied, exit here
        if (!source) return failure('no-image-source');

        // if already loaded image, replace current image
        if (state.file !== null) {
            dispatch('REQUEST_ABORT_IMAGE', {
                source,
                success,
                failure,
                options,
                resolveOnConfirm
            });
            return;
        }

        // reset state so we can start with clean slate when loading this file
        resetState(state);

        // create file state
        state.file = { uid: getUniqueId() };

        // requested image to be loaded
        dispatch('DID_REQUEST_LOAD_IMAGE', { source });

        // for now file should always be of type Blob or File
        loadImage$1(source, { progress: p => p !== null && dispatch('DID_MAKE_PROGRESS', { progress: p }) })
            .then(({ file, fileInstructions }) => {
                
                // no options received but did receive instructions with file load
                if (!options && fileInstructions) {
                    const { crop, filter, colorMatrix, color, markup, size } = fileInstructions;
                    options = {
                        crop,
                        filter: filter ? filter.id || filter.matrix : colorMatrix,
                        color,
                        markup,
                        size
                    };
                }
                
                // image is no longer valid
                if (shouldAbortImageLoad(state)) {
                    return dispatch('ABORT_IMAGE');
                }
                // set file name
                if (!file.name) {
                    file.name = getDateString();
                }
        
                // remember source
                state.file.orientation = -1;
                state.file.data = file;
        
                // load new image instead
                dispatch('LOAD_IMAGE', { success, failure, options, resolveOnConfirm }, true);

                dispatch('KICK');
            })
            .catch(error => {

                // image is no longer valid
                if (shouldAbortImageLoad(state)) return dispatch('ABORT_IMAGE');

                dispatch('DID_LOAD_IMAGE_ERROR', { error: { status: 'IMAGE_LOAD_ERROR', data: error }});

                failure(error);
            });

    },

    LOAD_IMAGE: ({ success, failure, options = {}, resolveOnConfirm }) => {

        // image is no longer valid
        if (shouldAbortImageLoad(state)) {
            return dispatch('ABORT_IMAGE');
        }        
        // load the file
        const file = state.file.data;
        
        // get orientation from JPEG exif data
        Promise.all([
            getImageSize(file),
            getImageOrientation(file)
        ])
            .then(([size, orientation]) => {

            // image is no longer valid
            if (shouldAbortImageLoad(state)) return dispatch('ABORT_IMAGE');

            // remember for later use
            state.file.orientation = query('GET_OUTPUT_CORRECT_IMAGE_EXIF_ORIENTATION') && shouldCorrectImageExifOrientation() ? orientation : -1;

            // if is rotated incorrectly swap width and height
            if (state.file.orientation > -1) {
                const { width, height } = size;
                if (orientation >= 5 && orientation <= 8) {
                    size.width = height;
                    size.height = width;
                }
            }

            // exit here if image is too small
            const minImageSize = query('GET_MIN_IMAGE_SIZE');
            if (size.width < minImageSize.width || size.height < minImageSize.height) {
                dispatch('DID_LOAD_IMAGE_ERROR', {
                    error: {
                        status: 'IMAGE_MIN_SIZE_VALIDATION_ERROR',
                        data: {
                            size, 
                            minImageSize
                        }
                    }
                });
                resetState(state);
                failure();
                return;
            }

            // scale if necessary
            const limitedSize = scaleImageSize(size, {
                width: query('GET_MAX_IMAGE_PREVIEW_WIDTH'),
                height: query('GET_MAX_IMAGE_PREVIEW_HEIGHT')
            });

            // set image dimensions and other info
            state.image = {
                x: 0,
                y: 0,
                width: limitedSize.width,
                height: limitedSize.height,
                naturalWidth: size.width,
                naturalHeight: size.height,
                aspectRatio: size.height / size.width,
                orientation
            };

            // set user defined size (only used when width / height is supplied)
            if (options.size) {

                // handle output size
                if ((options.size.hasOwnProperty('mode') && options.size.hasOwnProperty('upscale'))) {
                    state.options.outputWidth = options.size.width;
                    state.options.outputHeight = options.size.height;
                    state.options.outputFit = options.size.mode;
                    state.options.upscale = options.size.upscale;
                }
                // handle user size
                else {
                    state.size.width = options.size.width;
                    state.size.height = options.size.height;
                    state.size.aspectRatioLocked = true;
                    state.size.aspectRatioPrevious = false;
                    state.instructions.size = {
                        width: options.size.width,
                        height: options.size.height
                    };
                }
                
            }

            // remember original crop requirements for this image (so we can reset)
            state.instructions.crop = getBaseCropInstructions(
                query, 
                state, 
                options.crop ? { ...options.crop } : null,
                state.size
            );

            //#if !_EXCLUDE_CROP_UTIL
            // determine if should unlock crop selection limit
            state.crop.limitToImageBounds = state.options.cropLimitToImageBounds;
            if (state.instructions.crop.scaleToFit === false) {
                state.crop.limitToImageBounds = state.instructions.crop.scaleToFit;
            }
            //#endif

            //#if !_EXCLUDE_FILTER_UTIL
            // set filter instructions
            if (typeof options.filter === 'undefined') {
                state.instructions.filter = state.options.filter;
            }
            else {
                const f = options.filter;
                state.instructions.filter = f === null ? f :  f.id || f.matrix || f;
            }
            //#endif

            //#if !_EXCLUDE_MARKUP_UTIL
            // set markup instructions
            const initialMarkup = state.options.markup || [];
            state.instructions.markup = initialMarkup.concat(options.markup || []);
            //#endif

            //#if !_EXCLUDE_COLOR_UTIL
            // set color instructions
            state.instructions.color = Object.keys(COLOR_TOOLS).reduce((obj, tool) => {
                obj[tool] = 
                    options.color && typeof options.color[tool] !== 'undefined' ? 
                        typeof options.color[tool] === 'number' ? options.color[tool] : options.color[tool].value : 
                        state.options[`color${capitalizeFirstLetter(tool)}`];
                return obj;
            }, {});
            //#endif

            // we done!
            dispatch('DID_LOAD_IMAGE', {
                image: {
                    size: state.file.data.size,
                    name: state.file.data.name,
                    type: state.file.data.type,
                    orientation,
                    ...size
                }
            });

            // image loaded
            state.filePromise = {
                resolveOnConfirm,
                success,
                failure
            };
        })
        .catch(error => {

            // image is no longer valid
            if (shouldAbortImageLoad(state)) {
                return dispatch('ABORT_IMAGE');
            }            
            dispatch('DID_LOAD_IMAGE_ERROR', { error: { status: 'IMAGE_UNKNOWN_ERROR', data: error }});
            
            resetState(state);

            failure();
        });

    },

    CHANGE_VIEW: ({ id }) => {
        state.activeView = id;
        dispatch('SHOW_VIEW', { id });
    },

    UPDATE_ROOT_RECT: ({ rect }) => {
        state.rootRect = rect;
    },

    DID_RESIZE_STAGE: ({ size, offset, animate }) => {

        const isFirstDraw = state.stage === null;

        // update stage dimensions based on new size, this allows centering of crop area
        state.stage = createRect(0, 0, size.width, size.height);
        state.stageOffset = createVector(offset.x, offset.y);

        // limit stage size to image size
        if (!query('GET_ALLOW_PREVIEW_FIT_TO_VIEW')) {
            const rect = query('GET_IMAGE_STAGE_RECT');
            state.stage = createRect(0, 0, rect.width, rect.height);
            state.stageOffset = createVector(
                state.stageOffset.x + rect.x, 
                state.stageOffset.y + rect.y
            );
        }
        
        // if we haven't drawn before, we need to reset the draw area, else we'll just recenter everything
        if (isFirstDraw) {
            
            reset(state, query, dispatch);

            // done!
            dispatch('DID_SHOW_IMAGE', {
                image: {
                    size: state.file.data.size,
                    name: state.file.data.name,
                    type: state.file.data.type,
                    orientation: state.image.orientation,
                    width: state.image.naturalWidth,
                    height: state.image.naturalHeight
                }
            });
            
            // the promise to handle ".load()"
            if (!state.filePromise.resolveOnConfirm) {

                const crop = getCropFromStateRounded(state.image, state.crop);

                const size = getOutputSize(query);

                state.filePromise.success({
                    crop,
                    image: {
                        orientation: state.file.orientation,
                    },
                    size,
                    output: {
                        type: query('GET_OUTPUT_TYPE'),
                        quality: query('GET_OUTPUT_QUALITY')
                    }
                });

            }

        }
        else {

            state.instantUpdate = !animate;
            recenter(state, query);

            setTimeout(() => {
                state.instantUpdate = false;
            }, 16);

        }
    },

    //#if !_EXCLUDE_RESIZE_UTIL
    RESIZE_SET_OUTPUT_SIZE_ASPECT_RATIO_LOCK: ({ value }) => {
        state.size.aspectRatioLocked = value;
    },

    RESIZE_SET_OUTPUT_SIZE: ({ width, height }) => {

        width = width || null;
        height = height || null;

        const limitedSize = limitSize(
            { width, height }, 
            query('GET_SIZE_MIN'), 
            query('GET_SIZE_MAX'), 
            query('GET_CROP_RECTANGLE_ASPECT_RATIO')
        );

        // save new values
        state.size.width = limitedSize.width ? Math.round(limitedSize.width) : null;
        state.size.height = limitedSize.height ? Math.round(limitedSize.height) : null;

        // update crop aspect ratio
        if (width && height) {

            const outputSizeAspectRatio = height / width;

            // aspect ratio is already correctly set
            if (outputSizeAspectRatio === state.crop.aspectRatio) return;

            // remember the current aspect ratio so we can fall back
            if (state.size.aspectRatioPrevious === false) {
                state.size.aspectRatioPrevious = state.crop.aspectRatio;
            }

            // set new aspect ratio based on image output dimensions
            dispatch('CROP_SET_ASPECT_RATIO', { value: outputSizeAspectRatio });
        }
        // restore previous aspect ratio (if was set)
        else if (state.size.aspectRatioPrevious !== false) {
            dispatch('CROP_SET_ASPECT_RATIO', { value: state.size.aspectRatioPrevious });
            state.size.aspectRatioPrevious = false;
        }
    },
    //#endif

    CROP_SET_ASPECT_RATIO: ({ value }) => {
        
        clearCenterTimeout(state);

        state.crop.aspectRatio = isString(value) ? getNumericAspectRatioFromString(value) : value;
        if (state.crop.aspectRatio) { recenter(state, query); }
        state.crop.isDirty = true;

        if (state.size.width && state.size.height) {

            if (state.crop.aspectRatio) {
                // if setting a fixed aspect ratio calculate new output size
                const heightAuto = state.size.width * state.crop.aspectRatio;
                const heightLimited = limit(heightAuto, query('GET_SIZE_MIN').height, query('GET_SIZE_MAX').height);
                state.size.height = heightLimited;
                state.size.width = heightLimited / state.crop.aspectRatio;
            }
            else {
                // if setting a free aspect ratio unlock height so can scale freely
                state.size.height = null;
            }
        }
    },

    DID_SET_CROP_ASPECT_RATIO: ({ value, prevValue }) => {
        const currentValue = getNumericAspectRatioFromString(value);
        const previousValue = getNumericAspectRatioFromString(prevValue);
        if (currentValue === previousValue) return;
        dispatch('CROP_SET_ASPECT_RATIO', { value });
    },

    CROP_ZOOM: () => {
        if (!state.stage) return;
        clearCenterTimeout(state);
        recenter(state, query);
    },

    //#if !_EXCLUDE_CROP_UTIL
    DID_SET_CROP_LIMIT_TO_IMAGE_BOUNDS: ({ value, prevValue }) => {
        state.crop.limitToImageBounds = value;
        if (prevValue === false && value === true) {
            dispatch('CROP_ENABLED_LIMIT_TO_IMAGE_BOUNDS');
        }
    },
    
    CROP_ENABLED_LIMIT_TO_IMAGE_BOUNDS: () => {

        // set default crop
        const { stage, image } = state;

        // safety net for when this function somehow gets called when the Doka script is compressed
        if (!state.crop.rectangle) return;

        // the current aspect ratio of the crop rectangle
        const currentCropRectAspectRatio = state.crop.rectangle.height / state.crop.rectangle.width;

        // get centered rect (we want to center in view when letter boxing)
        const centeredCropRect = getCenteredCropRect(stage, currentCropRectAspectRatio);

        // update state
        state.crop.rectangle = centeredCropRect;
        state.crop.transforms = limitImageTransformsToCropRect(
            image,
            state.crop.rectangle,
            state.crop.transforms,
            'moving'
        );
        state.crop.transforms = limitImageTransformsToCropRect(
            image,
            state.crop.rectangle,
            state.crop.transforms,
            'resizing'
        );
        state.crop.transforms = limitImageTransformsToCropRect(
            image,
            state.crop.rectangle,
            state.crop.transforms,
            'rotating'
        );
        state.crop.draft.rectangle = null;
        state.crop.draft.transforms = null;

        recenter(state, query);
    },

    CROP_SET_LIMIT: ({ value, silent = false }) => {
        const changed = state.crop.limitToImageBounds !== value;
        state.crop.limitToImageBounds = value;
        if (changed && !silent) {
            state.crop.isDirty = true;
        }
        if (changed && value) {
            dispatch('CROP_ENABLED_LIMIT_TO_IMAGE_BOUNDS');
        }
    },

    CROP_IMAGE_RESIZE_GRAB: () => {
        copyConfirmed(state);
        clearCenterTimeout(state);
    },

    CROP_IMAGE_ROTATE_GRAB: () => {
        copyConfirmed(state); 
        clearCenterTimeout(state); 
        state.crop.isRotating = true;
    },
    
    CROP_RECT_DRAG_GRAB: () => {
        copyConfirmed(state);
        clearCenterTimeout(state);
    },

    CROP_RECT_DRAG_RELEASE: () => {
        confirmCropDraft(state);
        startCenterTimeout(state, query, dispatch);
    },

    CROP_RECT_EDGE_DRAG: ({ offset, origin, anchor }) => {

        const { image, stage } = state;

        const direction = /n|s/.test(origin) ? Direction.VERTICAL : Direction.HORIZONTAL;
        const originCentered = getEdgeCenterCoordinates(origin, state.crop.rectangle);
        const anchorCentered = getEdgeCenterCoordinates(anchor, state.crop.rectangle);
        const target = vectorLimit({
            x: originCentered.x + (direction === Direction.HORIZONTAL ? offset.x : 0),
            y: originCentered.y + (direction === Direction.VERTICAL ? offset.y : 0)
        }, stage);

        // calculate min and max rectangle size based on current image zoom
        const minCropSize = query('GET_MIN_CROP_SIZE');
        const maxCropSize = query('GET_MAX_CROP_SIZE');

        minCropSize.width = roundFloat(minCropSize.width);
        minCropSize.height = roundFloat(minCropSize.height);

        const minScale = getMinScale(state.crop.rectangle, query('GET_MIN_PREVIEW_IMAGE_SIZE'));
        const cropScale = (state.crop.draft.transforms.scale || state.crop.transforms.scale);
        const maxSizeScalar = minScale / cropScale;

        maxCropSize.width = roundFloat(maxCropSize.width * maxSizeScalar);
        maxCropSize.height = roundFloat(maxCropSize.height * maxSizeScalar);
        
        const interactionSign = {
            x: Math.sign(originCentered.x - anchorCentered.x),
            y: Math.sign(originCentered.y - anchorCentered.y)
        };

        state.crop.draft.rectangle = getEdgeTargetRect(interactionSign, target, direction, anchorCentered, rectBounds(state.crop.rectangle), rectBounds(stage), state.crop.aspectRatio, minCropSize, maxCropSize);
        
        if (!state.crop.limitToImageBounds) return;
        state.crop.draft.transforms = getImageTransformsFromRect(image, state.crop.draft.rectangle.limited, state.crop.transforms);
    },

    CROP_RECT_CORNER_DRAG: ({ offset, origin, anchor }) => {

        const { image, stage } = state;

        const originPosition = getCornerCoordinates(origin, state.crop.rectangle);
        const anchorPosition = getCornerCoordinates(anchor, state.crop.rectangle);
        const target = {
            x: originPosition.x + offset.x,
            y: originPosition.y + offset.y
        };

        // calculate min and max rectangle size based on current image zoom
        const minCropSize = query('GET_MIN_CROP_SIZE');
        const maxCropSize = query('GET_MAX_CROP_SIZE');

        minCropSize.width = roundFloat(minCropSize.width);
        minCropSize.height = roundFloat(minCropSize.height);

        const minScale = getMinScale(state.crop.rectangle, query('GET_MIN_PREVIEW_IMAGE_SIZE'));
        const cropScale = (state.crop.draft.transforms.scale || state.crop.transforms.scale);
        const maxSizeScalar = minScale / cropScale;

        maxCropSize.width = roundFloat(maxCropSize.width * maxSizeScalar);
        maxCropSize.height = roundFloat(maxCropSize.height * maxSizeScalar);
        
        const interactionSign = {
            x: Math.sign(originPosition.x - anchorPosition.x),
            y: Math.sign(originPosition.y - anchorPosition.y)
        };

        state.crop.draft.rectangle = getCornerTargetRect(interactionSign, target, anchorPosition, stage, state.crop.aspectRatio, minCropSize, maxCropSize);
        
        if (!state.crop.limitToImageBounds) return;
        state.crop.draft.transforms = getImageTransformsFromRect(image, state.crop.draft.rectangle.limited, state.crop.transforms);
    },

    CROP_IMAGE_DRAG_GRAB: () => copyConfirmed(state) || clearCenterTimeout(state),
    CROP_IMAGE_DRAG_RELEASE: () => { confirmImageDraft(state); resetRotationScale(state); startCenterTimeout(state, query, dispatch); },
    CROP_IMAGE_ROTATE_RELEASE: () => { confirmImageDraft(state); startCenterTimeout(state, query, dispatch); },
    CROP_IMAGE_DRAG: ({ value }) => {
        clearCenterTimeout(state);
        state.crop.draft.transforms = getTranslateTransforms(
            state.image,
            state.crop.rectangle,
            state.crop.transforms,
            value,
            state.crop.limitToImageBounds
        );
    },

    CROP_IMAGE_RESIZE_RELEASE: () => {
        
        // match crop rectangle to image aspect ratio when zooming out
        if (query('GET_CROP_RESIZE_MATCH_IMAGE_ASPECT_RATIO')) {
            correctCropRectangleByResize(state, query);
        }

        confirmImageDraft(state);
        resetRotationScale(state);
        startCenterTimeout(state, query, dispatch);
    },

    CROP_IMAGE_RESIZE: ({ value }) => {
        clearCenterTimeout(state);
        const transforms = state.crop.transforms;
        state.crop.draft.targetSize = transforms.scale + (transforms.scale * value);
        state.crop.draft.transforms = getResizeTransforms(
            state.image,
            state.crop.rectangle,
            transforms,
            state.crop.draft.targetSize,
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            state.crop.limitToImageBounds
        );
    },

    CROP_IMAGE_RESIZE_SET: ({ value }) => {

        // from 1 to infinity, compensate by calculating base zoom

        const scalar = Math.max(state.crop.rectangle.width / state.image.width, state.crop.rectangle.height / state.image.height);

        clearCenterTimeout(state);
        const transforms = state.crop.transforms;
        state.crop.draft.targetSize = value * scalar;
        state.crop.draft.transforms = getResizeTransforms(
            state.image,
            state.crop.rectangle,
            transforms,
            state.crop.draft.targetSize,
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            state.crop.limitToImageBounds
        );
    },

    CROP_IMAGE_RESIZE_MULTIPLY: ({ value }) => {
        clearCenterTimeout(state);

        const transforms = state.crop.transforms;
        state.crop.draft.targetSize = transforms.scale * value;

        // const updateResizeTransforms = () => {
        state.crop.draft.transforms = getResizeTransforms(
            state.image,
            state.crop.rectangle,
            transforms,
            state.crop.draft.targetSize,
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            state.crop.limitToImageBounds
        );
    },

    CROP_IMAGE_RESIZE_AMOUNT: ({ value }) => {
        clearCenterTimeout(state);
        const transforms = state.crop.transforms;
        state.crop.draft.targetSize = (state.crop.draft.transforms ? state.crop.draft.transforms.scale : transforms.scale) + value;
        state.crop.draft.transforms = getResizeTransforms(
            state.image,
            state.crop.rectangle,
            transforms,
            state.crop.draft.targetSize,
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            state.crop.limitToImageBounds
        );
    },

    CROP_IMAGE_ROTATE:({ value }) => {
        clearCenterTimeout(state);
        state.crop.isRotating = true;
        rotate(
            state, 
            {
                main: state.crop.rotation.main,
                sub: value
            },
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            false,
            state.crop.limitToImageBounds
        );
    },

    CROP_IMAGE_ROTATE_ADJUST: ({ value }) => {
        clearCenterTimeout(state);
        rotate(
            state, 
            {
                main: state.crop.rotation.main,
                sub: Math.min(Math.PI / 4, Math.max(-Math.PI / 4,state.crop.rotation.sub + value))
            },
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            false,
            state.crop.limitToImageBounds
        );
        confirmImageDraft(state);
    },

    CROP_IMAGE_ROTATE_CENTER: () => {
        clearCenterTimeout(state);
        rotate(
            state, 
            {
                main: state.crop.rotation.main,
                sub: 0
            }, 
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            false,
            state.crop.limitToImageBounds
        );
        confirmImageDraft(state);
    },

    CROP_IMAGE_ROTATE_LEFT: () => {
        resetCenterTimeout(state, query, dispatch);
        rotate(
            state,
            {
                main: state.crop.rotation.main - TURN$1,
                sub: state.crop.rotation.sub
            },
            query('GET_MIN_PREVIEW_IMAGE_SIZE'), 
            -TURN$1,
            state.crop.limitToImageBounds
        );
        confirmImageDraft(state);
        if (query('GET_CROP_FORCE_LETTERBOX')) {
            dispatch('CROP_UPDATE_LETTERBOX');
        }
    },

    CROP_IMAGE_ROTATE_RIGHT: () => {
        resetCenterTimeout(state, query, dispatch);
        rotate(
            state, 
            {
                main: state.crop.rotation.main + TURN$1,
                sub: state.crop.rotation.sub
            },
            query('GET_MIN_PREVIEW_IMAGE_SIZE'),
            TURN$1,
            state.crop.limitToImageBounds
        );
        confirmImageDraft(state);
        if (query('GET_CROP_FORCE_LETTERBOX')) {
            dispatch('CROP_UPDATE_LETTERBOX');
        }
    },

    CROP_IMAGE_FLIP_HORIZONTAL: () => {
        resetCenterTimeout(state, query, dispatch);
        if (roundFloat(state.crop.rotation.main%Math.PI/2, 5) === 0) {
            state.crop.flip.horizontal = !state.crop.flip.horizontal;
        }
        else {
            state.crop.flip.vertical = !state.crop.flip.vertical;
        }
        state.crop.isDirty = true;
    },

    CROP_IMAGE_FLIP_VERTICAL: () => {
        resetCenterTimeout(state, query, dispatch);
        if (roundFloat(state.crop.rotation.main%Math.PI/2, 5) === 0) {
            state.crop.flip.vertical = !state.crop.flip.vertical;
        }
        else {
            state.crop.flip.horizontal = !state.crop.flip.horizontal;
        }
        state.crop.isDirty = true;
    },
    //#endif

    DID_RECEIVE_IMAGE_DATA: ({ previewData, thumbData }) => {
        state.file.preview = previewData;
        state.file.thumb = thumbData;
    },

    //#if !_EXCLUDE_MARKUP_UTIL
    MARKUP_SET_VALUE: ({ value }) => {
        state.markup = (value || []).map(prepareMarkup).sort(sortMarkupByZIndex);
    },

    MARKUP_ADD_DEFAULT: ({ value }) => {

        const getRandomRange = () => -.5 + Math.random();

        const cropAspectRatio = query('GET_CROP_RECTANGLE_ASPECT_RATIO');

        const defaultX = .5;
        const defaultY = .5;
        
        const defaultSize = .5;
        const defaultWidth = cropAspectRatio > 1 ? defaultSize / cropAspectRatio : defaultSize;
        const defaultHeight = cropAspectRatio > 1 ? defaultSize : defaultSize * cropAspectRatio;

        const defaultScalar = .5;

        const getDefaultRect = () => ({
            width: defaultWidth,
            height: defaultHeight,
            x: (defaultX + (getRandomRange() * defaultScalar)) - (defaultWidth * .5),
            y: (defaultY + (getRandomRange() * defaultScalar)) - (defaultHeight * .5)
        });

        const getStyleValue = (style) => {
            const toolValues = query('GET_MARKUP_TOOL_VALUES');
            return toolValues[style];
        };

        const getDefaultShape = () => {
            const shapeStyle = getStyleValue('shapeStyle');
            const color = getStyleValue('color');
            return {
                backgroundColor: shapeStyle[0] || shapeStyle[1] ? null : color,
                borderWidth: shapeStyle[0],
                borderStyle: shapeStyle[1] ? shapeStyle[1] : null,
                borderColor: color
            }
        };

        const markup = {
            rect: () => ({
                ...getDefaultRect(),
                ...getDefaultShape()
            }),
            ellipse: () => ({
                ...getDefaultRect(),
                ...getDefaultShape()
            }),
            text: () => ({
                x: (.5 + (getRandomRange() * .5)) - .1,
                y: (.5 + (getRandomRange() * .5)),
                width: 0,
                height: 0,
                fontColor: getStyleValue('color'),
                fontSize: getStyleValue('fontSize'),
                fontFamily: getStyleValue('fontFamily'),
                text: 'Text'
            }),
            line: () => {
                const lineStyle = getStyleValue('lineStyle');
                return {
                    ...getDefaultRect(),
                    lineColor: getStyleValue('color'),
                    lineWidth: lineStyle[0],
                    lineStyle: lineStyle[1] ? lineStyle[1] : null,
                    lineDecoration: getStyleValue('lineDecoration')
                }
            }
        }[value]();

        dispatch('MARKUP_ADD', [value, markup]);
    },

    MARKUP_ADD: (markup) => {

        // deselect all
        state.markup.forEach(element => element[1].isSelected = false);
        
        // clean up removed markup
        state.markup = state.markup.filter(element => !element[1].isDestroyed);

        // select prepared markup
        const preparedMarkup = prepareMarkup(markup);

        // add new markup
        state.markup.push(preparedMarkup);

        // sort
        state.markup.sort(sortMarkupByZIndex);

        if (query('GET_MARKUP_UTIL') !== 'draw') {

            // fire select so it's also selected in the view
            dispatch('MARKUP_SELECT', { id: preparedMarkup[1].id });

        }

        state.crop.isDirty = true;
    },

    MARKUP_SELECT: ({ id }) => {
        state.markup.forEach(el => {
            el[1].isSelected = el[1].id === id;
            el[1].isDirty = true;
        });
    },

    MARKUP_ELEMENT_DRAG: ({ id, origin, offset, size }) => {
        const el = state.markup.find(el => el[1].id === id);
        if (!el) return;
        const settings = el[1];

        const ox = origin.x / size.width;
        const oy = origin.y / size.height;
        const ow = origin.width / size.width;
        const oh = origin.height / size.height;
        const dx = offset.x / size.width;
        const dy = offset.y / size.height;

        settings.x = ox + dx;
        settings.y = oy + dy;
        settings.width = ow;
        settings.height = oh;
        settings.left = undefined;
        settings.top = undefined;
        settings.right = undefined;
        settings.bottom = undefined;

        settings.isDirty = true;

        state.crop.isDirty = true;
    },

    MARKUP_ELEMENT_RESIZE: ({ id, corner, origin, offset, size }) => {

        const el = state.markup.find(el => el[1].id === id);
        if (!el) return;
        const [type, settings] = el;

        let dx = (origin.x + offset.x) / size.width;
        let dy = (origin.y + offset.y) / size.height;

        if (/n/.test(corner)) {
            if (type === 'line') {
                settings.height = settings.height - (dy - settings.y);
                settings.y = dy;
            }
            else {
                const bottom = settings.y + settings.height;
                if (dy > bottom) dy = bottom;
                settings.height = settings.height - (dy - settings.y);
                settings.y = dy;
            }
        }
        if (/w/.test(corner)) {
            if (type === 'line') {
                settings.width = settings.width - (dx - settings.x);
                settings.x = dx;
            }
            else {
                const right = settings.x + settings.width;
                if (dx > right) dx = right;
                settings.width = settings.width - (dx - settings.x);
                settings.x = dx;
            }
        }
        if (/s/.test(corner)) {
            if (type === 'line') {
                settings.height = dy - settings.y;
            }
            else {
                settings.height = Math.max(0, dy - settings.y);
            }
        }
        if (/e/.test(corner)) {
            if (type === 'line') {
                settings.width = dx - settings.x;
            }
            else {
                settings.width = Math.max(0, dx - settings.x);
            }
        }

        settings.left = undefined;
        settings.top = undefined;
        settings.right = undefined;
        settings.bottom = undefined;

        settings.isDirty = true;

        state.crop.isDirty = true;
    },

    MARKUP_DELETE: ({ id }) => {

        const el = state.markup.find(el => el[1].id === id);
        if (!el) return;
        const settings = el[1];

        if (settings.allowDestroy) {
            settings.isDestroyed = true;
            settings.isSelected = false;
            settings.isDirty = true;
        }

        let nextId = null;
        let l = state.markup.length;
        while (l>0) {
            l--;
            const settings = state.markup[l][1];

            // skip already destroyed (marked for destruction) markup or markup that can't be destroyed
            if (settings.isDestroyed || !settings.allowDestroy) continue;

            // select as next element
            nextId = settings.id;
            break;
        }

        // fire select so it's also selected in the view
        dispatch('MARKUP_SELECT', { id: nextId });
    },

    MARKUP_UPDATE: ({ style, value }) => {

        // remember value
        state.markupToolValues[style] = value;

        // find selected markup
        state.markup
            .map(markup => markup[1])
            .filter(markup => markup.isSelected)
            .forEach(markup => {
                if (style === 'color') {
                    const colorProp = getColorProperty(markup);
                    markup[colorProp] = value;
                }
                else if (style === 'shapeStyle') {
                    const color = getColor(markup);
                    markup.borderWidth = value[0];
                    markup.borderStyle = value[1];
                    markup.backgroundColor = value[0] || value[1] ? null : color;
                }
                else if (style === 'lineStyle') {
                    markup.lineWidth = value[0];
                    markup.lineStyle = value[1];
                }
                else {
                    markup[style] = value;
                }
                markup.isDirty = true;
            });
        
        // mark crop as dirty so it's updated as well
        state.crop.isDirty = true;
    },

    ...['color', 'shapeStyle', 'lineStyle', 'textDecoration', 'fontSize', 'fontFamily'].reduce((actions, style) => {
        const styleUpper = style.split(/(?=[A-Z])/).join('_').toUpperCase();
        const styleCapitalized = capitalizeFirstLetter(style);
        actions['SET_MARKUP_' + styleUpper] = ({ value, prevValue }) => {
            if (value === prevValue) return;
            state.options[`markup${styleCapitalized}`] = value;
            dispatch('MARKUP_UPDATE', { style, value });
        };
        return actions;
    }, {}),

    //#endif

    DID_SET_CROP: ({ value, prevValue }) => {
        if (value === prevValue) return;
        dispatch('SET_DATA', { crop: value });
    },

    //#if !_EXCLUDE_COLOR_UTIL
    COLOR_SET_COLOR_VALUE:({ key, value }) => {
        state.crop.isDirty = true;
        dispatch('COLOR_SET_VALUE', { key, value });
    },

    COLOR_SET_VALUE:({ key, value }) => {
        state.colorValues[key] = value;
        dispatch('SET_COLOR_MATRIX', {
            key,
            matrix: COLOR_TOOLS[key](value)
        });
    },

    ...Object.keys(COLOR_TOOLS).reduce((actions, tool) => {
        const toolUpper = tool.toUpperCase();
        const toolCapitalized = capitalizeFirstLetter(tool);
        actions[`SET_COLOR_${toolUpper}`] = ({ value, prevValue }) => {
            if (value === prevValue) return;
            const [min, max] = query(`GET_COLOR_${toolUpper}_RANGE`);
            const limitedValue = limit(value, min, max);
            state.options[`color${toolCapitalized}`] = limitedValue;
            if (!state.instructions.color) {
                state.instructions.color = {};
            }
            state.instructions.color[tool] = limitedValue;
            dispatch('COLOR_SET_VALUE', {
                key: tool,
                value: limitedValue
            });
        };
        return actions;
    }, {}),

    SET_COLOR_MATRIX: ({ key, matrix }) => {
        // add or remove matrix based on if it's defined or not
        if (!matrix) {
            delete state.colorMatrices[key];
        }
        else {
            state.colorMatrices[key] = [...matrix];
        }
        dispatch('DID_SET_COLOR_MATRIX', { key, matrix });
    },
    //#endif

    //#if !_EXCLUDE_FILTER_UTIL
    FILTER_SET_FILTER: ({ value }) => {
        state.crop.isDirty = true;
        dispatch('FILTER_SET_VALUE', { value });
    },

    FILTER_SET_VALUE: ({ value }) => {
        let matrix = isColorMatrix(value) ? value : null;
        if (isString(value)) {
            // find matrix in filters
            const filters = query('GET_FILTERS');
            forin(filters, (key, filter) => {
                if (key === value) matrix = filter.matrix();
            });
        }
        state.filter = value;
        state.filterName = isString(value) ? value : null;
        dispatch('SET_COLOR_MATRIX', { key: 'filter', matrix });
    },

    DID_SET_UTIL: ({ value, prevValue }) => {
        if (state.options.utils.indexOf(value) === -1) return;
        dispatch('CHANGE_VIEW', { id: value });
    },

    DID_SET_FILTER: ({ value, prevValue })  => {
        if (value === prevValue) return;
        dispatch('FILTER_SET_VALUE', { value });
        dispatch('SET_DATA', { filter: value });
    },
    //#endif

    //#if !_EXCLUDE_RESIZE_UTIL
    DID_SET_SIZE: ({ value, prevValue }) => {
        if (value === prevValue) return;
        dispatch('SET_DATA', { size: value });
    },
    //#endif

    //#if !_EXCLUDE_MARKUP_UTIL
    DID_SET_MARKUP_UTIL: ({ value, prevValue }) => {
        if (value === prevValue) return;
        if (!value) return;
        if (!/^(draw|line|text|rect|ellipse)$/.test(value)) {
            value = 'select';
        }
        dispatch('SWITCH_MARKUP_UTIL', { util: value });
    },

    DID_SET_MARKUP: ({ value, prevValue }) => {
        if (value !== prevValue && JSON.stringify(value) === JSON.stringify(prevValue)) return;
        dispatch('MARKUP_SET_VALUE', { value });
        dispatch('SET_DATA', { markup: value });
    },
    //#endif

    SET_DATA: (options) => {

        //#if !_EXCLUDE_RESIZE_UTIL
        if (options.size) {
            
            const draftSize = {
                width: null,
                height: null,
                ...options.size
            };
            
            const limitedSize = limitSize(draftSize, query('GET_SIZE_MIN'), query('GET_SIZE_MAX'), null);

            state.instructions.size = {
                ...limitedSize
            };

            dispatch('RESIZE_SET_OUTPUT_SIZE', limitedSize);
        }
        //#endif

        //#if !_EXCLUDE_FILTER_UTIL
        if (options.filter) {
            // filter ? filter.id || filter.matrix : options.colorMatrix,
            state.instructions.filter = options.filter ? options.filter.id || options.filter.matrix : options.colorMatrix;
        }
        //#endif

        //#if !_EXCLUDE_MARKUP_UTIL
        state.instructions.markup = options.markup || [];
        state.instructions.markup.forEach(m => m[1].isDirty = true);
        //#endif

        //#if !_EXCLUDE_COLOR_UTIL
        state.instructions.color = Object.keys(COLOR_TOOLS).reduce((obj, tool) => {
            const isColorUndefined = typeof options.color === 'undefined' || typeof options.color[tool] === 'undefined';
            const defaultColorValue = state.options[`color${capitalizeFirstLetter(tool)}`];
            obj[tool] = isColorUndefined ? defaultColorValue : isNumber(options.color[tool]) ? options.color[tool] : options.color[tool].value;
            return obj;
        }, {});
        //#endif

        if (options.crop) {

            // remember original crop requirements (so we can reset)
            state.instructions.crop = getBaseCropInstructions(
                query,
                state,
                options.crop,
                state.size
            );

            // determine if should unlock crop selection limit
            state.crop.limitToImageBounds = state.options.cropLimitToImageBounds;
            if (state.instructions.crop.scaleToFit === false) {
                state.crop.limitToImageBounds = state.instructions.crop.scaleToFit;
            }

            resetCrop(state, query);
        }

    },

    DID_SET_INITIAL_STATE: ({ value }) => {

        const { crop, filter, color, size = {}, markup = [] } = value || {};

        // size first as it might impact aspect ratio of crop
        const draftSize = {
            width: null,
            height: null,
            ...size
        };

        const limitedSize = limitSize(draftSize, query('GET_SIZE_MIN'), query('GET_SIZE_MAX'), null);
        state.instructions.size = {
            ...limitedSize
        };

        // crop
        state.instructions.crop = getBaseCropInstructions(query, state, crop);

        // determine if should unlock crop selection limit
        state.crop.limitToImageBounds = state.options.cropLimitToImageBounds;
        if (state.instructions.crop.scaleToFit === false) {
            state.crop.limitToImageBounds = state.instructions.crop.scaleToFit;
        }
        
        //#if !_EXCLUDE_FILTER_UTIL
        // filter
        state.instructions.filter = filter || null;
        //#endif

        //#if !_EXCLUDE_COLOR_UTIL
        // color
        state.instructions.color = Object.keys(COLOR_TOOLS).reduce((obj, tool) => {
            obj[tool] = typeof color === 'undefined' || typeof color[tool] === 'undefined' ? state.options[`color${capitalizeFirstLetter(tool)}`] : color[tool];
            return obj;
        }, {});
        //#endif

        //#if !_EXCLUDE_MARKUP_UTIL
        // markup
        state.instructions.markup = markup;
        //#endif

        state.crop.isDirty = true;
    },

    GET_DATA: ({ success, failure, file, data }) => {
        
        // image has not fully loaded
        if (!state.file) return failure('no-image-source');
        if (!state.stage) return failure('image-not-fully-loaded');

        const value = {
            file: isBoolean(file) ? file : query('GET_OUTPUT_FILE'),
            data: isBoolean(data) ? data : query('GET_OUTPUT_DATA'),
            success,
            failure
        };

        dispatch(value.file ? 'REQUEST_PREPARE_OUTPUT' : 'PREPARE_OUTPUT', value);
    },

    REQUEST_PREPARE_OUTPUT: ({ file, data, success, failure }) => {
        dispatch('PREPARE_OUTPUT', { file, data, success, failure }, true);
        dispatch('DID_REQUEST_PREPARE_OUTPUT');
    },

    PREPARE_OUTPUT: ({ file, data, success = () => {}, failure = () => {} }) => {

        if (shouldAbortImageLoad(state)) {
            return dispatch('ABORT_IMAGE');
        }
        const done = (output) => {

            dispatch('DID_PREPARE_OUTPUT');
                    
            if (shouldAbortImageLoad(state)) {
                return dispatch('ABORT_IMAGE');
            }
            success(output);
        };

        const fail = (err) => {
            if (shouldAbortImageLoad(state)) {
                return dispatch('ABORT_IMAGE');
            }            failure(err);
        };

        prepareOutput({ file, data }, state, query)
            .then(output => {

                // allow injecting any kind of post processing
                const { afterCreateOutput } = state.options;
                const promisedOutput = afterCreateOutput 
                    ? afterCreateOutput(
                        output, 
                        (label, progress) => {
                            dispatch('DID_REQUEST_POSTPROCESS_OUTPUT', { label, progress });
                            return (progress) => {
                                dispatch('DID_MAKE_PROGRESS', { progress });
                            }
                        }
                    )
                    : output;
                
                Promise.resolve(promisedOutput)
                    .then(done)
                    .catch(error => {
                        dispatch('DID_REQUEST_POSTPROCESS_OUTPUT_ERROR', { error });
                    });
                
            })
            .catch(fail);
      
    },

    EDIT_RESET: () => {
        clearCenterTimeout(state);
        reset(state, query, dispatch);
    },

    EDIT_CONFIRM: () => {

        // image has not fully loaded
        if (!state.file || !state.stage) return;

        clearCenterTimeout(state);

        dispatch('CROP_ZOOM');

        const value = {
            file: query('GET_OUTPUT_FILE'),
            data: query('GET_OUTPUT_DATA'),
            success: (output) => {
                if (state.filePromise.resolveOnConfirm) {
                    state.filePromise.success(output);
                }
                dispatch('DID_CONFIRM', { output });
            },
            failure: console.error
        };
        dispatch(value.file ? 'REQUEST_PREPARE_OUTPUT' : 'PREPARE_OUTPUT', value);
    },

    EDIT_CANCEL: () => {
        if (state.filePromise) {
            state.filePromise.success(null);
        }
        dispatch('DID_CANCEL');
    },

    EDIT_CLOSE: () => {
        clearCenterTimeout(state);
    },

    EDIT_DESTROY: () => {
        resetState(state);
    },

    // default action for setting options
    SET_OPTIONS: ({ options }) => {
        forin(options, (key, value) => {
            dispatch(`SET_${fromCamels(key, '_').toUpperCase()}`, { value });
        });
    }

});

const createIcon = (svg, size = 24) => `<svg width="${size}" height="${size}" viewBox="0 0 ${size} ${size}" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">${svg}</svg>`;

const button = createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    name: 'button',
    mixins: {
        styles: [
            'opacity'
        ],
        animations: {
            'opacity': { type: 'tween', duration: 250 }
        },
        apis: ['id'],
        listeners: true
    },
    tag: 'button',
    create: ({ root, props }) => {
        
        root.element.innerHTML = `${ props.icon || ''}<span>${props.label}</span>`;

        root.element.setAttribute('type', props.type || 'button');

        if (props.name) {
            props.name.split(' ').forEach(name => {
                root.element.className += ` doka--button-${name}`;
            });
        }

        root.ref.handleClick = () => {
            typeof props.action === 'string' ? root.dispatch(props.action) : props.action();
        };

        root.element.addEventListener('click', root.ref.handleClick);

        root.ref.handlePointer = e => e.stopPropagation();
        root.element.addEventListener('pointerdown', root.ref.handlePointer);

        if (props.create) {
            props.create({ root, props });
        }
    },
    destroy:({ root }) => {

        root.element.removeEventListener('pointerdown', root.ref.handlePointer);

        root.element.removeEventListener('click', root.ref.handleClick);
    }
});

const textNode = (tag) => createView({
    ignoreRect: true,
    tag,
    create: ({ root, props }) => {
        root.element.textContent = props.text;
    }
});

const progressIndicator = createView({
    name: 'status-progress',
    tag: 'svg',
    ignoreRect: true,
    ignoreRectUpdate: true,
    mixins: {
        apis: [
            'progress'
        ],
        animations: {
            'progress': { type: 'spring', stiffness: .25, damping: .25, mass: 2.5 },
        }
    },
    create: ({ root, props }) => {

        root.element.setAttribute('data-value', 0);
        root.element.setAttribute('width', 24);
        root.element.setAttribute('height', 24);
        root.element.setAttribute('viewBox', '0 0 20 20');
        const circle = root.ref.circle = document.createElementNS('http://www.w3.org/2000/svg', 'circle');
        const attributes = {
            r: 5,
            cx: 10,
            cy: 10,
            fill: 'none',
            stroke: 'currentColor',
            'stroke-width': 10,
            transform: 'rotate(-90) translate(-20)'
        };
        root.element.appendChild(circle);
        
        Object.keys(attributes).forEach(key => {
            circle.setAttribute(key, attributes[key]);
        });

        root.ref.updateStroke = progress => {
            root.ref.circle.setAttribute('stroke-dasharray', `${Math.min(1, progress) * 31.42} 31.42`);
        };

        if (typeof(props.progress) === 'number') {
            root.progress = props.progress;
            root.element.setAttribute('data-value', Math.max(props.progress, .001));
            root.ref.updateStroke(root.progress);
        }
        else {
            root.progress = 0;
        }

    },
    write: createRoute({
        DID_MAKE_PROGRESS: ({ root, action }) => {
            root.progress = action.progress;
            root.element.setAttribute('data-value', Math.max(action.progress, .001));
        }
    }, ({ root }) => {
        root.ref.updateStroke(root.progress);
    })
});

const statusBubbleInner = createView({
    name: 'status-bubble-inner',
    create: ({ root, props }) => {

        if (props.onClose) {
            root.appendChildView(
                root.createChildView(button, {
                    label: 'Close',
                    name: 'icon-only status-bubble-close',
                    icon: createIcon('<g fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></g>'),
                    action: props.onClose
                })
            );
        }
        else {
            root.ref.progressIndicator = root.appendChildView(
                root.createChildView(progressIndicator, { progress: props.progress })
            );
        }
        
        root.appendChildView(
            root.createChildView(textNode('p'), {
                text: props.label
            })
        );

    }
});

const statusBubble = createView({
    name: 'status-bubble',
    mixins: {
        styles: [
            'opacity',
            'translateY'
        ],
        apis: [
            'markedForRemoval'
        ],
        animations: {
            'opacity': { type: 'tween', duration: 500 },
            'translateY': { type:'spring', mass: 20 }
        }
    },
    create: ({ root, props }) => root.appendChildView(root.createChildView(statusBubbleInner, props))
});

const hideBusyIndicators = (root) => {
    root.element.dataset.viewStatus = 'idle';
    hideBusyIndicatorsAnimated(root);
};

const hideBusyIndicatorsAnimated = (root) => {
    root.ref.busyIndicators.forEach(indicator => {
        indicator.translateY = -10;
        indicator.opacity = 0;
        indicator.markedForRemoval = true;
    });
};

const showBusyIndicator = (root, label, onClose, progress) => {
    root.element.dataset.viewStatus = 'busy';
    const indicator = addBusyIndicator(root, label, onClose, progress);
    hideBusyIndicatorsAnimated(root);
    root.ref.busyIndicators.push(indicator);
    indicator.markedForRemoval = false;
    indicator.translateY = 0;
    indicator.opacity = 1;
};

const addBusyIndicator = (root, label, onClose = false, progress = null) => root.appendChildView(
    root.createChildView(statusBubble, {
        translateY: 20,
        opacity: 0,
        label,
        onClose,
        progress
    })
);

const editStatus = createView({
    name: 'edit-status',
    ignoreRect: true,
    create: ({ root }) => {
        root.ref.busyIndicators = [];
        root.element.setAttribute('tabindex', -1);
    },
    write: createRoute({
        MISSING_WEBGL: ({ root }) => {
            const showCloseButton = /fullscreen/.test(root.query('GET_STYLE_LAYOUT_MODE'));
            showBusyIndicator(root, root.query('GET_LABEL_STATUS_MISSING_WEB_G_L'), showCloseButton ? () => {
                root.dispatch('EDIT_CANCEL');
            } : null);
        },
        AWAITING_IMAGE: ({ root }) => {
            showBusyIndicator(root, root.query('GET_LABEL_STATUS_AWAITING_IMAGE'));
        },
        DID_PRESENT_IMAGE: ({ root }) => {
            hideBusyIndicators(root);
        },
        DID_LOAD_IMAGE_ERROR: ({ root, action }) => {
            const showCloseButton = /fullscreen/.test(root.query('GET_STYLE_LAYOUT_MODE'));
            const labelProp = root.query('GET_LABEL_STATUS_LOAD_IMAGE_ERROR');
            const label = typeof labelProp === 'function' ? labelProp(action.error) : labelProp;
            showBusyIndicator(root, label, showCloseButton ? () => {
                root.dispatch('EDIT_CANCEL');
            } : null);
        },
        DID_REQUEST_LOAD_IMAGE: ({ root }) => {
            showBusyIndicator(root, root.query('GET_LABEL_STATUS_LOADING_IMAGE'));
        },
        DID_REQUEST_PREPARE_OUTPUT: ({ root }) => {
            showBusyIndicator(root, root.query('GET_LABEL_STATUS_PROCESSING_IMAGE'));
        },
        DID_REQUEST_POSTPROCESS_OUTPUT: ({ root, action }) => {
            showBusyIndicator(root, action.label, null, action.progress);
        },
        DID_REQUEST_POSTPROCESS_OUTPUT_ERROR: ({ root, action }) => {
            const label = action.error;
            showBusyIndicator(root, label, () => root.dispatch('DID_PREPARE_OUTPUT'));
        },
        DID_PREPARE_OUTPUT: ({ root }) => {
            hideBusyIndicators(root);
        }
    }),
    didWriteView:({ root }) => {
        root.ref.busyIndicators = root.ref.busyIndicators.filter(indicator => {
            if (indicator.markedForRemoval && indicator.opacity === 0) {
                root.removeChildView(indicator);
                return false;
            }
            return true;
        });
    }
});

const Interaction = {
    'down': 'pointerdown',
    'move': 'pointermove',
    'up': 'pointerup',
};

const createPointerRegistry = () => {
    const pointers = [];
    const getPointerIndex = e => pointers.findIndex(pointer => pointer.pointerId === e.pointerId);
    const isRegisteredPointer = e => getPointerIndex(e) >= 0;
    return {
        update: e => {
            const i = getPointerIndex(e);
            if (i < 0) return;
            pointers[i] = e;
        },
        multiple: () => pointers.length > 1,
        count: ()=> pointers.length,
        active: () => pointers.concat(),
        push: e => {
            if (isRegisteredPointer(e)) return;
            pointers.push(e);
        },
        pop: e => {
            const i = getPointerIndex(e);
            if (i < 0) return;
            pointers.splice(i, 1);
        }
    }
};

const addEvent$1 = (node, event, handler, options) => node.addEventListener(Interaction[event], handler, options);
const removeEvent$1 = (node, event, handler) => node.removeEventListener(Interaction[event], handler);

const contains = (parent, element) => {
    if ('contains' in parent) parent.contains(element);
    let el = element;
    do {
        if (el === parent) return true;
    }
    // eslint-disable-next-line no-cond-assign
    while(el = el.parentNode);
    return false;
};

const createDragger = (control, grab, drag, release, options = {
    stopPropagation: true,
    cancelOnMultiple: false }) => {

    const origin = {
        x: 0,
        y: 0
    };

    const state = {
        enabled: true,
        origin: null,
        cancel: false,
        cancelled: false,
        pointers: createPointerRegistry()
    };

    const offset = e => ({
        x: e.pageX - origin.x,
        y: e.pageY - origin.y,
    });

    let frame = null;
    const update = (e, fn) => {
        if (!fn) return;
        if (!frame) updateFire(e, fn);
        cancelAnimationFrame(frame);
        frame = requestAnimationFrame(() => {
            updateFire(e, fn);
            frame = null;
        });
    };

    const updateFire = (e, fn) => fn.apply(null, [e, offset(e)]);
    
    const dragStart = (e) => {

        const isFirstInteraction = state.pointers.count() === 0;

        // reset if is new page interaction
        if (isFirstInteraction) {
            state.active = false;
            state.cancel = false;
            state.cancelled = false;
        }

        // needs to target control
        if (control !== e.target && !contains(control, e.target)) return;

        // if this is the second interaction, ignore
        if (!isFirstInteraction) {
            if (options.cancelOnMultiple) {
                state.cancel = true;
            }
            return;
        }

        // don't deal with non-primary interactions
        if (!e.isPrimary) return;

        // now interacting
        state.pointers.push(e);
        addEvent$1(document.documentElement, 'up', dragStop);

        e.preventDefault();

        if (options.stopPropagation) {
            e.stopPropagation();
            e.stopImmediatePropagation();
        }

        state.active = true;

        origin.x = e.pageX;
        origin.y = e.pageY;

        addEvent$1(document.documentElement, 'move', dragBusy);

        grab(e);
    };

    const dragBusy = (e) => {

        // ignore non primary interactions
        if (!e.isPrimary) return;

        if (state.cancelled) return;

        e.preventDefault();
        
        if (options.stopPropagation) {
            e.stopPropagation();
        }
        
        update(e, drag);

        if (state.cancel) {
            state.cancelled = true;
            update(e, release);
        }
    };

    const dragStop = (e) => {

        state.pointers.pop(e);

        // clean up when last pointer released
        if (state.pointers.count() === 0) {
            removeEvent$1(document.documentElement, 'move', dragBusy);
            removeEvent$1(document.documentElement, 'up', dragStop);
        }

        // not the primary interaction
        if (!state.active) return;

        // was cancelled earlier
        if (state.cancelled) return;

        e.preventDefault();
        
        if (options.stopPropagation) {
            e.stopPropagation();
        }

        update(e, drag);

        update(e, release);
    };
    
    addEvent$1(document.documentElement, 'down', dragStart);

    return {
        enable: () => {
            if (!state.enabled) {
                addEvent$1(document.documentElement, 'down', dragStart);
            }
            state.enabled = true;
        },
        disable: () => {
            if (state.enabled) {
                removeEvent$1(document.documentElement, 'down', dragStart);
            }
            state.enabled = false;
        },
        destroy: () => {
            removeEvent$1(document.documentElement, 'up', dragStop);
            removeEvent$1(document.documentElement, 'move', dragBusy);
            removeEvent$1(document.documentElement, 'down', dragStart);
        }
    }
};

const imageOverlaySpring = {
    type: 'spring',
    stiffness: .4,
    damping: .65,
    mass: 7
};

//#if !_EXCLUDE_MARKUP_UTIL

const activateMarkupUtil = (root, props, util) => {

    if (/^(line|text|ellipse|rect)$/.test(util)) {

        // add default shape
        root.dispatch('MARKUP_ADD_DEFAULT', { value: util });

        // switch back to select
        root.dispatch('SET_MARKUP_UTIL', { value: 'select' });

    }
    else if (util === 'draw' && !root.ref.drawInput) {

        const { drawState, viewSize } = root.ref;

        let startX = 0;
        let startY = 0;
        let brush = {};
        let radius = root.query('GET_MARKUP_DRAW_DISTANCE');

        root.ref.drawInput = createDragger(
            root.element,
            (e) => {

                const toolValues = root.query('GET_MARKUP_TOOL_VALUES');
                const lineWidth = toolValues.lineStyle[0];
                const lineStyle = toolValues.lineStyle[1];

                drawState.lineColor = toolValues.color;
                drawState.lineWidth = lineWidth;
                drawState.lineStyle = lineStyle;

                const rootRect = root.query('GET_ROOT');
                
                const offsetX = typeof e.offsetX !== 'undefined' ? e.offsetX : (e.pageX - rootRect.x - props.stageOffsetX) - window.pageXOffset;
                const offsetY = typeof e.offsetY !== 'undefined' ? e.offsetY : (e.pageY - rootRect.y - props.stageOffsetY) - window.pageYOffset;
                
                startX = offsetX - root.markupX;
                startY = offsetY - root.markupY;

                brush.x = 0;
                brush.y = 0;
                
                drawState.points.push({
                    x: startX / viewSize.width,
                    y: startY / viewSize.height
                });

            },
            (e, pointer) => {

                // need to kick the draw loop to keep updating the view
                root.dispatch('KICK');

                // no input radius, just use current pointer
                if (!radius) {
                    drawState.points.push({
                        x: (startX + pointer.x) / viewSize.width,
                        y: (startY + pointer.y) / viewSize.height
                    });
                    return;
                }

                // distance between brush and pointer
                const dist = vectorDistance(pointer, brush);

                // if is larger than radius move brush towards pointer
                if (dist > radius) {


                    // get angle between brush and pointer
                    const angle = vectorAngleBetween(brush, pointer);
                    const angleRotated = angle + (Math.PI / 2);

                    // move brush towards pointer at angle
                    const moveDist = radius - dist;
                    brush.x += Math.sin(angleRotated) * moveDist;
                    brush.y -= Math.cos(angleRotated) * moveDist;

                    // add point
                    drawState.points.push({
                        x: (startX + brush.x) / viewSize.width,
                        y: (startY + brush.y) / viewSize.height
                    });
                }

            },
            (e, offset) => {

                // has drawn
                if (drawState.points.length > 1) {

                    // confirm path (clean up drawstate and add actual markup)
                    root.dispatch('MARKUP_ADD', ['path', { ...drawState }]);

                }

                // clear draw state
                drawState.points = [];
            }
        );
    }

    // clean up draw input 
    if (util !== 'draw' && root.ref.drawInput) {
        root.ref.drawInput.destroy();
        root.ref.drawInput = null;
    }

};

const getColor$1 = (settings) => {
    const { fontColor, backgroundColor, lineColor, borderColor } = settings;
    return fontColor || backgroundColor || lineColor || borderColor;
};

const MARKUP_MARGIN = 10;

const setAttributes$1 = (element, attr$$1) => Object.keys(attr$$1).forEach(key => {
    element.setAttribute(key, attr$$1[key]);
});

const ns$2 = 'http://www.w3.org/2000/svg';
const svg$1 = (tag, attr$$1) => {
    const element = document.createElementNS(ns$2, tag);
    if (attr$$1) {
        setAttributes$1(element, attr$$1);
    }
    return element;
};

const LINE_CORNERS = ['nw', 'se'];

const RECT_CORNERS = ['nw', 'n', 'ne', 'w', 'e', 'sw', 's', 'se'];
const CORNER_CURSOR = {
    nw: 'nwse',
    n: 'ns',
    ne: 'nesw',
    w: 'ew',
    e: 'ew',
    sw: 'nesw',
    s: 'ns',
    se: 'nwse'
};

const CORNER_COORDINATES = {
    nw: rect => ({x: rect.x, y: rect.y}),
    n: rect => ({x: rect.x + (rect.width * .5), y: rect.y}),
    ne: rect => ({x: rect.x + rect.width, y: rect.y}),
    w: rect => ({x: rect.x, y: rect.y + (rect.height *.5)}),
    e: rect => ({x: rect.x + rect.width, y: rect.y + (rect.height * .5)}),
    sw: rect => ({x: rect.x, y: rect.y + rect.height}),
    s: rect => ({x: rect.x + (rect.width * .5), y: rect.y + rect.height}),
    se: rect => ({x: rect.x + rect.width, y: rect.y + rect.height}),
};

const imageMarkup = createView({
    tag: 'div',
    name: 'image-markup',
    ignoreRect: true,
    mixins: {
        styles: [
            'opacity'
        ],
        animations: {
            'opacity': 'spring',
            'markupX': imageOverlaySpring,
            'markupY': imageOverlaySpring,
            'markupWidth': imageOverlaySpring,
            'markupHeight': imageOverlaySpring,
        },
        listeners: true,
        apis: [
            'toolsReference',
            'onSelect',
            'onDrag',
            'markupX',
            'markupY',
            'markupWidth',
            'markupHeight',
            'allowInteraction',
            'stageOffsetX',
            'stageOffsetY'
        ]
    },
    create: ({ root, props }) => {

        const { onSelect = () => {}, onUpdate = () => {} } = props;

        const canvas = svg$1('svg', { 'xmlns': 'http://www.w3.org/2000/svg', 'xmlns:xlink': 'http://www.w3.org/1999/xlink' });
        root.ref.canvas = canvas;

        const rootSize = root.query('GET_ROOT_SIZE');
        canvas.setAttribute('width', rootSize.width);
        canvas.setAttribute('height', rootSize.height);

        const input = document.createElement('input');
        setAttributes$1(input, {
            'type': 'text',
            'autocomplete': 'off',
            'autocapitalize': 'off'
        });
        input.addEventListener('keydown', e => {
            e.stopPropagation();

            if (e.keyCode === 13 || e.keyCode === 9) {
                e.target.blur();
                deselect();
            }

            else if (e.keyCode === 8 && !root.ref.input.value.length) {
                root.dispatch('MARKUP_DELETE', { id: root.ref.selected.id });
            }
            
        });

        root.ref.input = input;
        root.ref.elements = [];
        root.ref.viewSize = {
            width: 0,
            height: 0,
            scale: 0
        };


        // the active selected item
        root.ref.resetSelected = () => {
            root.ref.selected = {
                id: null,
                type: null,
                settings: {}
            };
            return root.ref.selected;
        };

        root.ref.resetSelected();

        const isMarkupUtil = (element) => 
            contains(root.ref.manipulatorGroup, element) ||
            element === root.ref.input;

        const isSelectedMarkup = (element) => 
            root.ref.selected.id === getMarkup(element).id;
        
        const getMarkup = (element) => element.id ? element : element.parentNode;

        const deselect = () => {
            root.ref.resetSelected();
            onSelect(null);
        };

        root.ref.handleDeselect = e => {

            if (!root.query('IS_ACTIVE_VIEW', 'markup')) return;

            if (!root.ref.selected.id) return;

            if (e.target === root.ref.removeButton.element) return;

            // if is selected element don't deselect
            if (isSelectedMarkup(e.target)) return;

            // if is tools don't deselect
            if (isMarkupUtil(e.target) || props.isMarkupUtil(e.target)) return;

            deselect();
        };

        addEvent$1(document.body, 'down', root.ref.handleDeselect);

        root.ref.handleTextInput = () => onUpdate('text', root.ref.input.value);
        root.ref.input.addEventListener('input', root.ref.handleTextInput);

        root.ref.handleAttemptDelete = e => {

            if (!root.query('IS_ACTIVE_VIEW', 'markup')) return;
            
            if (root.ref.selected.id && (e.keyCode === 8 || e.keyCode === 46)) {
                e.stopPropagation();
                e.preventDefault();
                root.dispatch('MARKUP_DELETE', { id: root.ref.selected.id });
            }
        };
        document.body.addEventListener('keydown', root.ref.handleAttemptDelete);


        const shapeOffsetGroup = svg$1('g');

        const shapeGroup = svg$1('g', {
            'class': 'doka--shape-group'
        });
        shapeOffsetGroup.appendChild(shapeGroup);
        root.ref.shapeGroup = shapeGroup;

        const manipulatorGroup = svg$1('g', {
            fill: 'none',
            'class': 'doka--manipulator-group'
        });

        const manipulatorRect = svg$1('rect', {
            x:0, y:0, width:0, height:0,
            fill: 'none'
        });

        const manipulatorPath = svg$1('path');

        manipulatorGroup.appendChild(manipulatorPath);
        manipulatorGroup.appendChild(manipulatorRect);
        
        root.ref.manipulatorPath = manipulatorPath;
        root.ref.manipulatorRect = manipulatorRect;

        root.ref.manipulators = [];
        for (let i =0;i<10;i++) {

            const elVisual = svg$1('circle', {
                r: 6,
                'stroke-width': 2
            });

            const elHitbox = svg$1('circle', {
                r: 22,
                'class': 'doka--hitbox',
                style: 'opacity: 0'
            });

            manipulatorGroup.appendChild(elHitbox);
            manipulatorGroup.appendChild(elVisual);
            
            root.ref.manipulators.push({
                visual: elVisual,
                hitbox: elHitbox,
                dragger: null
            });
        }

        shapeOffsetGroup.appendChild(manipulatorGroup);
        root.ref.manipulatorGroup = manipulatorGroup;
        canvas.appendChild(shapeOffsetGroup);

        root.ref.shapeOffsetGroup = shapeOffsetGroup;

        // remove button
        root.ref.removeButton = root.appendChildView(root.createChildView(button, {
            label: root.query('GET_LABEL_MARKUP_REMOVE_SHAPE'),
            name: 'destroy-shape',
            action: () => {
                root.dispatch('MARKUP_DELETE', { id: root.ref.selected.id });
            }
        }));

        // is now active
        if (root.query('IS_ACTIVE_VIEW', 'markup')) {
            root.element.dataset.active = true;
        }

        // drawer
        root.ref.drawInput = null;
        root.ref.drawState = {
            lineColor: null,
            lineWidth: null,
            lineStyle: null,
            points: []
        };

        const drawPath = svg$1('path', {
            'fill': 'none',
            'class': 'doka--draw-path'
        });
        root.ref.drawPath = drawPath;
        canvas.appendChild(drawPath);

        // clip
        const clip = createElement('div', 'doka--image-markup-clip');
        clip.appendChild(input);
        clip.appendChild(canvas);
        root.ref.clip = clip;
        
        // add canvas
        root.element.appendChild(clip);

        // set default tool
        if (root.query('GET_MARKUP_UTIL') === 'draw') {
            activateMarkupUtil(root, props, 'draw');
        }
    },
    destroy: ({ root }) => {

        root.ref.elements.concat(root.ref.manipulators).forEach(manipulator => {
            if (!manipulator.dragger) return;
            manipulator.dragger.destroy();
        });

        root.ref.input.removeEventListener('input', root.ref.handleTextInput);
        
        document.body.removeEventListener('keydown', root.ref.handleAttemptDelete);

        removeEvent$1(document.body, 'down', root.ref.handleDeselect);
    },
    read: ({ root }) => {

        if (root.rect.element.hidden) return;

        for (let key in root.ref.elements) {
            const el = root.ref.elements[key];
            if (!el || el.nodeName !== 'text' || !el.parentNode) continue;
            const bbox = el.getBBox();
            el.bbox = {
                x: bbox.x,
                y: bbox.y,
                width: bbox.width,
                height: bbox.height
            };
        }
    },
    write: createRoute({
        SHOW_VIEW: ({ root, props, action }) => {
            if (action.id === 'markup') {
                root.element.dataset.active = true;
            }
            else {
                root.element.dataset.active = false;
                props.onSelect(null);
            }
        },
        MARKUP_SET_VALUE: ({ root }) => {

            forin(root.ref.elements, (key, element) => {
                if (!element) return;
                if (element.dragger) {
                    element.dragger.destroy();
                }
            });

            root.ref.elements = [];

            root.ref.shapeGroup.innerHTML = '';
        },
        UPDATE_ROOT_RECT: ({ root, action }) => {
            const { canvas } = root.ref;
            canvas.setAttribute('width', action.rect.width);
            canvas.setAttribute('height', action.rect.height);
            root.ref.previousScale = null;
        },
        SWITCH_MARKUP_UTIL: ({ root, action, props }) => {

            const { util } = action;

            activateMarkupUtil(root, props, util);
        }
    }, ({ root, props, timestamp }) => {

        // only when visible
        if (root.opacity <= 0) return;

        // don't update when waiting on view info
        const view = root.query('GET_CROP', props.id, timestamp);
        if (!view) return;

        // current util
        const activeUtil = root.query('GET_MARKUP_UTIL');
        root.element.dataset.util = activeUtil || '';

        const { markup, cropStatus } = view;
        const { onSelect, onDrag } = props;
        const { clip, manipulatorGroup, drawPath, viewSize, shapeOffsetGroup, manipulators, manipulatorPath, manipulatorRect, removeButton, drawState } = root.ref;

        let outputWidth = root.query('GET_OUTPUT_WIDTH');
        let outputHeight = root.query('GET_OUTPUT_HEIGHT');

        const { image, crop } = cropStatus;
        let currentWidth = crop.width;
        let currentHeight = crop.height;
        let cropRectangleAspectRatio = crop.widthFloat / crop.heightFloat;

        if (outputWidth || outputHeight) {
            const outputFit = root.query('GET_OUTPUT_FIT');
            if (outputWidth && !outputHeight) {
                outputHeight = outputWidth;
            }
            if (outputHeight && !outputWidth) {
                outputWidth = outputHeight;
            }

            let scalarWidth = outputWidth / currentWidth;
            let scalarHeight = outputHeight / currentHeight;

            if (outputFit === 'force') {
                currentWidth = outputWidth;
                currentHeight = outputHeight;
            }
            else {
                let scalar;
                if (outputFit === 'cover') {
                    scalar = Math.max(scalarWidth, scalarHeight);
                }
                else if (outputFit === 'contain') {
                    scalar = Math.min(scalarWidth, scalarHeight);
                }
                currentWidth = currentWidth * scalar;
                currentHeight = currentHeight * scalar;
            }
        }
        else {
            if (image.width && image.height) {
                currentWidth = image.width;
                currentHeight = image.height;
            }
            else if (image.width && !image.height) {
                currentWidth = image.width;
                currentHeight = image.width / cropRectangleAspectRatio;
            }
            else if (image.height && !image.width) {
                currentHeight = image.height;
                currentWidth = image.height * cropRectangleAspectRatio;
            }
        }

        const drawLength = drawState.points.length;
        const markupX = roundFloat(root.markupX, 3);
        const markupY = roundFloat(root.markupY, 3);
        const markupWidth = roundFloat(root.markupWidth, 3);
        const markupHeight = roundFloat(root.markupHeight, 3);
        const scale = roundFloat(Math.min(root.markupWidth / currentWidth, root.markupHeight / currentHeight), 4);

        viewSize.width = markupWidth;
        viewSize.height = markupHeight;
        viewSize.scale = scale;

        if (!stateHasChanged(root, { drawLength, markupX, markupY, scale, markup, currentWidth, currentHeight })) return;

        // clip markup area
        const left = markupX;
        const right = root.rect.element.width - markupX - markupWidth;
        const top = markupY;
        const bottom = root.rect.element.height - markupY - markupHeight;
        const path = `inset(${top}px ${right}px ${bottom}px ${left}px)`;
        clip.style.clipPath = path;
        clip.style.webkitClipPath = path; // Safari <= 13
        
        // move markup
        shapeOffsetGroup.setAttribute('transform', `translate(${markupX} ${markupY})`);

        root.ref.previousDrawLength = drawLength;
        root.ref.previousX = markupX;
        root.ref.previousY = markupY;
        root.ref.previousScale = scale;
        root.ref.previousCurrentHeight = currentHeight;
        root.ref.previousCurrentWidth = currentWidth;
        root.ref.previousMarkupLength = markup.length;

        if (viewSize.width < 1 || viewSize.height < 1) return;

        const selectedMarkup = markup.find(markup => markup[1].isSelected);
        const selectionChanged = (selectedMarkup && root.ref.selected.id !== selectedMarkup[1].id) || (root.ref.selected.id && !selectedMarkup);

        let selected;
        if (selectedMarkup) {
            selected = root.ref.selected = {
                id: selectedMarkup[1].id,
                type: selectedMarkup[0],
                settings: selectedMarkup[1]
            };
        }
        else {
            selected = root.ref.resetSelected();
        }

        // draw draw path, if path is being drawn, markup doesn't need any updates
        if (drawState.points.length) {
            const styles = getMarkupStyles(drawState, viewSize, scale);

            styles.d = pointsToPathShape(drawState.points.map(point => ({
                x: markupX + (point.x * viewSize.width),
                y: markupY + (point.y * viewSize.height)
            })));

            setAttributes$1(drawPath, styles);
            return;
        }
        else {
            drawPath.removeAttribute('d');
        }

        // always show input when text field selected
        root.ref.input.hidden = root.ref.selected.type !== 'text';

        // hide manipulators by default
        removeButton.element.dataset.active = root.ref.selected.id !== null;
        manipulatorPath.setAttribute('style', 'opacity:0');
        manipulatorRect.setAttribute('style', 'opacity:0');
        manipulators.forEach(manipulator => {
            manipulator.visual.setAttribute('style', 'opacity:0; pointer-events:none;');
            manipulator.hitbox.setAttribute('style', 'pointer-events:none;');
        });

        const markupFilter = root.query('GET_MARKUP_FILTER');
        markup.filter(markupFilter).sort(sortMarkupByZIndex).forEach((markup, index) => {

            const [type, settings] = markup;
            const { id, isDestroyed, isDirty, isSelected, allowSelect, allowMove, allowResize, allowInput } = settings;

            // if should remove
            if (isDestroyed) {
                const element = root.ref.elements[id];
                if (element) {
                    if (element.dragger) {
                        element.dragger.destroy();
                    }
                    root.ref.elements[id] = null;
                    element.parentNode.removeChild(element);
                }
                return;
            }

            // get element reference
            let element = root.ref.elements[id];
            if (!element) {
                
                // add element
                element = createMarkupByType(type, settings);
                root.ref.elements[id] = element;

                // make draggable
                if (allowSelect) {

                    let origin;
                    let downStamp;
                    let wasSelected;

                    element.dragger = createDragger(
                        element,
                        () => {
                            downStamp = Date.now();
                            origin = { ...element.rect };
                            wasSelected = id === root.ref.selected.id;
                            if (!wasSelected) {
                                onSelect(id);
                            }
                        },
                        (e, offset) => {
                            if (!allowMove) return;
                            onDrag(id, origin, offset, viewSize, scale);
                        },
                        (e, offset) => {
    
                            // can't edit, exit!
                            if (!allowInput) return;

                            // can only edit text shapes
                            if (type !== 'text') return;
    
                            // will wait for second click
                            if (!wasSelected) return;

                            const dist = vectorDistanceSquared({ x:0, y:0 }, offset);
                            const duration = Date.now() - downStamp;

                            // dragged to far to be a click
                            if (dist > 10) return;
    
                            // waited to long to be a click
                            if (duration > 750) return;

                            // if is text, allow editing
                            root.ref.input.focus();

                            // set cursor position
                            const elementOffsetX = root.markupX + element.bbox.x;
                            const elementWidth = element.bbox.width;
                            const interactionOffsetX = e.offsetX;
                            const offsetX = interactionOffsetX - elementOffsetX;
                            const offsetRelative = offsetX / elementWidth;
                            const charIndex = Math.round(root.ref.input.value.length * offsetRelative);
                            root.ref.input.setSelectionRange(charIndex, charIndex);
                        }
                    );

                    // disable dragger by default
                    element.dragger.disable();
                }
                else {
                    element.setAttribute('style', 'pointer-events:none;');
                }
            }
            
            // enable dragger
            if (element.dragger) {
                if (props.allowInteraction) {
                    element.dragger.enable();
                }
                else {
                    element.dragger.disable();
                }
            }

            // sort
            if (index !== element.index) {
                element.index = index;
                const shapeGroup = root.ref.shapeGroup;
                shapeGroup.insertBefore(element, shapeGroup.childNodes[index + 1]);
            }

            // update the element view
            if (isDirty) {
                updateMarkupByType(element, type, settings, viewSize, scale);
            }
            
            // is selected
            if (isSelected) {

                let removeButtonWidth = removeButton.rect.element.width;
                let removeButtonHeight = removeButton.rect.element.height;
                let removeButtonX = root.markupX - (removeButtonWidth * .5);
                let removeButtonY = root.markupY - removeButtonHeight - 15;

                let rect = type === 'text' ? element.bbox : element.rect;
                
                let isBrightColor = false;
                const color = getColor$1(settings);
                if (color) {
                    const rgb = toRGBColorArray(color);
                    isBrightColor = (.2126 * rgb[0] + .7152 * rgb[1] + .0722 * rgb[2]) / 255 > .65;
                    manipulatorGroup.setAttribute('is-bright-color', isBrightColor);
                }
                
                if (type === 'line') {
                    removeButtonX += rect.x;
                    removeButtonY += rect.y;
                    setAttributes$1(manipulatorPath, {
                        d: `M ${rect.x} ${rect.y} L ${rect.x + rect.width} ${rect.y + rect.height}`,
                        style: 'opacity:1'
                    });
                }
                else if (type === 'path') {
                    rect = {
                        x: settings.points[0].x * viewSize.width,
                        y: settings.points[0].y * viewSize.height,
                        width: 0,
                        height: 0
                    };
                    removeButtonX += rect.x;
                    removeButtonY += rect.y;
                    setAttributes$1(manipulatorPath, {
                        d: pointsToPathShape(settings.points.map(point => ({
                            x: (point.x * viewSize.width),
                            y: (point.y * viewSize.height)
                        }))),
                        style: 'opacity:1'
                    });
                }
                else if (rect) {
                    removeButtonX += rect.x + (rect.width * .5);
                    removeButtonY += rect.y;
                    setAttributes$1(manipulatorRect, {
                        x: rect.x - (type === 'text' ? 5 : 0),
                        y: rect.y,
                        width: rect.width + (type === 'text' ? 10 : 0),
                        height: rect.height,
                        style: 'opacity:1'
                    });
                }

                const topEdge = root.markupY + MARKUP_MARGIN;
                const bottomEdge = root.markupY + root.markupHeight - MARKUP_MARGIN;
                const leftEdge = root.markupX + MARKUP_MARGIN;
                const rightEdge = root.markupX + root.markupWidth - MARKUP_MARGIN;

                if (removeButtonY < topEdge) {
                    removeButtonY = topEdge;
                }
                else if (removeButtonY + removeButtonHeight > bottomEdge) {
                    removeButtonY = bottomEdge - removeButtonHeight;
                }

                if (removeButtonX < leftEdge) {
                    removeButtonX = leftEdge;
                }
                else if (removeButtonX + removeButtonWidth > rightEdge) {
                    removeButtonX = rightEdge - removeButtonWidth;
                }
                
                // position remove button
                if (!rect) {
                    removeButton.element.dataset.active = 'false';
                }

                removeButton.element.setAttribute('style',  `transform: translate3d(${removeButtonX}px, ${removeButtonY}px, 0)`);

                // show input at position of text area
                if (type === 'text' && rect) {

                    const width = rect.width + 65;
                    const maxWidth = root.markupWidth - rect.x;

                    let inputStyle = `
                        width: ${Math.min(width, maxWidth)}px;
                        height: ${rect.height}px;
                        color: ${element.getAttribute('fill')};
                        font-family: ${element.getAttribute('font-family')};
                        font-size: ${element.getAttribute('font-size').replace(/px/,'')}px;
                        font-weight: ${element.getAttribute('font-weight') || 'normal'};
                    `;

                    if (isIOS()) {
                        inputStyle += `
                            left: ${Math.round(root.markupX + rect.x)}px;
                            top: ${Math.round(root.markupY + rect.y)}px;
                        `;
                    }
                    else {
                        inputStyle += `
                            transform: translate3d(${Math.round(root.markupX + rect.x)}px,${Math.round(root.markupY + rect.y)}px,0);
                        `;
                    }

                    root.ref.input.setAttribute('style', inputStyle);
                    
                    // use left, top instead of transform because of weird iOS bug
                    element.setAttribute('fill', 'none');    
                    
                }

                // can't resize text
                if (type === 'text') return;

                // if not allowed to resize, we done
                if (!allowResize) return;

                // render resize corners at correct locations
                const corners = type === 'line' ? LINE_CORNERS : RECT_CORNERS;
                manipulators.forEach((manipulator, index) => {

                    const corner = corners[index];
                    if (!corner) return;

                    const cursor = type === 'line' ? 'move' : `${CORNER_CURSOR[corner]}-resize`;
                    const coordinates = CORNER_COORDINATES[corner](element.rect);

                    setAttributes$1(manipulator.visual, {
                        cx: coordinates.x,
                        cy: coordinates.y,
                        style: `opacity:1`
                    });

                    setAttributes$1(manipulator.hitbox, {
                        cx: coordinates.x,
                        cy: coordinates.y,
                        style: `cursor:${cursor}`
                    });
                
                });

            }
            
            // now drawn so no longer dirty
            settings.isDirty = false;
        });

        if (selectionChanged) {

            // remove previous dragger
            destroyElementControls(root);

            if (selected.type === 'text') {                    
                root.ref.input.value = selected.settings.text;
            }
            // create drag controls for selected element
            else if (root.ref.selected.id) {
                setupElementControls(root, props.onResize);
            }
        }
        
    })

});

const markAllAsDirty = markup => markup.forEach(m => m[1].isDirty = true);

const stateHasChanged = (root, { drawLength, markup, markupX, markupY, currentWidth, currentHeight, scale }) => {

    if (drawLength !== root.ref.previousDrawLength) {
        return true;
    }

    if (markupX !== root.ref.previousX) {
        markAllAsDirty(markup);
        return true;
    }

    if (markupY !== root.ref.previousY) {
        markAllAsDirty(markup);
        return true;
    }

    if (scale !== root.ref.previousScale) {
        markAllAsDirty(markup);
        return true;
    }

    if (currentHeight !== root.ref.previousCurrentHeight) {
        markAllAsDirty(markup);
        return true;
    }

    if (currentWidth !== root.ref.previousCurrentWidth) {
        markAllAsDirty(markup);
        return true;
    }

    if (markup.length !== root.ref.previousMarkupLength) {
        return true;
    }

    if (markup.find(markup => markup[1].isDirty)) {
        return true;
    }

    return false;
};

const setupElementControls = (root, onResize) => {

    const id = root.ref.selected.id;
    const element = root.ref.elements[id];
    
    // link up shape manipulation controls
    const corners = element.nodeName === 'g' ? LINE_CORNERS : RECT_CORNERS;
    root.ref.manipulators.forEach((manipulator, index) => {
        const corner = corners[index];
        if (!corner) return;
        let origin = null;
        manipulator.dragger = createDragger(
            manipulator.hitbox,
            () => {
                origin = {
                    x: parseFloat(attr(manipulator.hitbox, 'cx')),
                    y: parseFloat(attr(manipulator.hitbox, 'cy'))
                };
            },
            (e, offset) => {
                onResize(id, corner, origin, offset, root.ref.viewSize);
            },
            null,
            {
                stopPropagation: true
            }
        );
    });

};

const destroyElementControls = (root) => {
    root.ref.manipulators.forEach(el => {
        if (!el.dragger) return;
        el.dragger.destroy();
        el.dragger = null;
    });
};

//#endif

const KEY_MAP = {
    38: 'up',
    40: 'down',
    37: 'left',
    39: 'right',
    189: 'minus',
    187: 'plus',
    72: 'h',
    76: 'l',
    81: 'q',
    82: 'r',
    84: 't',
    86: 'v',
    90: 'z',
    219: 'left_bracket',
    221: 'right_bracket'
};

const createKeyboard = (control, downBefore, map, downAfter, up) => {

    let result = null;
    let first = true;
    
    const state = {
        enabled: true
    };

    const keyDown = e => {
        const key = KEY_MAP[e.keyCode] || e.keyCode;
        if (!map[key]) return;
        e.stopPropagation();
        if (first) {
            result = downBefore(key);
            first = false;
        }
        map[key](result);
        downAfter(result);
    };

    const keyUp = e => {
        const key = KEY_MAP[e.keyCode] || e.keyCode;
        if (!map[key]) return;
        e.stopPropagation();
        up(result);
        first = true;
    };

    control.addEventListener('keydown', keyDown);
    control.addEventListener('keyup', keyUp);

    return {
        enable: () => {
            if (!state.enabled) {
                control.addEventListener('keydown', keyDown);
                control.addEventListener('keyup', keyUp);
            }
            state.enabled = true;
        },
        disable: () => {
            if (state.enabled) {
                control.removeEventListener('keydown', keyDown);
                control.removeEventListener('keyup', keyUp);
            }
            state.enabled = false;
        },
        destroy:() => {
            control.removeEventListener('keydown', keyDown);
            control.removeEventListener('keyup', keyUp);
        }
    }

};

// draws the preview image to canvas
const createPreviewImage = (data, width, height, orientation = 1, target) => {

    // can't draw on half pixels
    width = Math.round(width);
    height = Math.round(height);

    // draw image
    const canvas = target || document.createElement('canvas');
    const ctx = canvas.getContext('2d');

    // if is rotated incorrectly swap width and height
    if (orientation >= 5 && orientation <= 8) {
        canvas.width = height;
        canvas.height = width;
    } else {
        canvas.width = width;
        canvas.height = height;
    }

    ctx.save();

    // correct image orientation
    if (orientation !== -1) {
        ctx.transform.apply(ctx, getImageOrientationMatrix(width, height, orientation));
    }

    // draw the image
    ctx.drawImage(data, 0, 0, width, height);

    // end draw image
    ctx.restore();

    return canvas;
};

/* javascript-obfuscator:disable */
const BitmapWorker = function() {
    // eslint-disable-next-line no-restricted-globals
    self.onmessage = e => {
        createImageBitmap(e.data.message.file).then(bitmap => {
            // eslint-disable-next-line no-restricted-globals
            self.postMessage({ id: e.data.id, message: bitmap }, [bitmap]);
        });
    };
};
/* javascript-obfuscator:enable */

const isBitmap = file => /^image/.test(file.type) && !/svg/.test(file.type);

const canCreateImageBitmap = file => {

    // Firefox versions before 58 will freeze when running createImageBitmap
    // in a Web Worker so we detect those versions and return false for support
    const userAgent = window.navigator.userAgent;
    const isFirefox = userAgent.match(/Firefox\/([0-9]+)\./);
    const firefoxVersion = isFirefox ? parseInt(isFirefox[1]) : null;
    if (firefoxVersion <= 58) return false;
    
    return 'createImageBitmap' in window && isBitmap(file);
};

const loadImage$2 = (url) =>
    new Promise((resolve, reject) => {
        const img = new Image();
        img.onload = () => {
            resolve(img);
        };
        img.onerror = e => {
            reject(e);
        };
        img.src = url;
    });

const compileShader = (gl, src, type) => {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, src);
    gl.compileShader(shader);
    return shader;
};

const createProgram = (gl, vertexShader, fragmentShader) => {
    const program = gl.createProgram();
    gl.attachShader(program, compileShader(gl, vertexShader, gl.VERTEX_SHADER));
    gl.attachShader(program, compileShader(gl, fragmentShader, gl.FRAGMENT_SHADER));
    gl.linkProgram(program);
    return program;
};

const createTexture = (gl, uniform, uniformSize, index, imageData) => {
	const texture = gl.createTexture();
	gl.activeTexture(gl.TEXTURE0 + index);
	gl.bindTexture(gl.TEXTURE_2D, texture);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.LINEAR);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_S, gl.CLAMP_TO_EDGE);
	gl.texParameteri(gl.TEXTURE_2D, gl.TEXTURE_WRAP_T, gl.CLAMP_TO_EDGE);
	gl.uniform1i(uniform, index);
	gl.uniform2f(uniformSize, imageData.width, imageData.height);
	try {
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, imageData);
	}
	catch(e) {
		gl.texImage2D(gl.TEXTURE_2D, 0, gl.RGBA, imageData.width, imageData.height, 0, gl.RGBA, gl.UNSIGNED_BYTE, null);
	}
	return texture;
};

const create = () => {
    const mat = new Float32Array(16);
    mat[0] = 1;
    mat[5] = 1;
    mat[10] = 1;
    mat[15] = 1;
    return mat;
};

const perspective = (mat, fovy, aspect, near, far) => {
    const f = 1.0 / Math.tan(fovy / 2);
    const nf = 1 / (near - far);
    mat[0] = f / aspect;
    mat[1] = 0;
    mat[2] = 0;
    mat[3] = 0;
    mat[4] = 0;
    mat[5] = f;
    mat[6] = 0;
    mat[7] = 0;
    mat[8] = 0;
    mat[9] = 0;
    mat[11] = -1;
    mat[12] = 0;
    mat[13] = 0;
    mat[15] = 0;
    mat[10] = (far + near) * nf;
    mat[14] = (2 * far * near) * nf;
};

const translate = (mat, v) => {
    const x = v[0];
    const y = v[1];
    const z = v[2];
    mat[12] = mat[0] * x + mat[4] * y + mat[8] * z + mat[12];
    mat[13] = mat[1] * x + mat[5] * y + mat[9] * z + mat[13];
    mat[14] = mat[2] * x + mat[6] * y + mat[10] * z + mat[14];
    mat[15] = mat[3] * x + mat[7] * y + mat[11] * z + mat[15];
};

const scale = (mat, v) => {
    const x = v[0];
    const y = v[1];
    const z = v[2];
    mat[0] = mat[0] * x;
    mat[1] = mat[1] * x;
    mat[2] = mat[2] * x;
    mat[3] = mat[3] * x;
    mat[4] = mat[4] * y;
    mat[5] = mat[5] * y;
    mat[6] = mat[6] * y;
    mat[7] = mat[7] * y;
    mat[8] = mat[8] * z;
    mat[9] = mat[9] * z;
    mat[10] = mat[10] * z;
    mat[11] = mat[11] * z;
};

const rotateX = (mat, rad) => {
    const s = Math.sin(rad);
    const c = Math.cos(rad);
    const a10 = mat[4];
    const a11 = mat[5];
    const a12 = mat[6];
    const a13 = mat[7];
    const a20 = mat[8];
    const a21 = mat[9];
    const a22 = mat[10];
    const a23 = mat[11];
    mat[4] = a10 * c + a20 * s;
    mat[5] = a11 * c + a21 * s;
    mat[6] = a12 * c + a22 * s;
    mat[7] = a13 * c + a23 * s;
    mat[8] = a20 * c - a10 * s;
    mat[9] = a21 * c - a11 * s;
    mat[10] = a22 * c - a12 * s;
    mat[11] = a23 * c - a13 * s;
};

const rotateY = (mat, rad) => {
    const s = Math.sin(rad);
    const c = Math.cos(rad);
    const a00 = mat[0];
    const a01 = mat[1];
    const a02 = mat[2];
    const a03 = mat[3];
    const a20 = mat[8];
    const a21 = mat[9];
    const a22 = mat[10];
    const a23 = mat[11];
    mat[0] = a00 * c - a20 * s;
    mat[1] = a01 * c - a21 * s;
    mat[2] = a02 * c - a22 * s;
    mat[3] = a03 * c - a23 * s;
    mat[8] = a00 * s + a20 * c;
    mat[9] = a01 * s + a21 * c;
    mat[10] = a02 * s + a22 * c;
    mat[11] = a03 * s + a23 * c;
};

const rotateZ = (mat, rad) => {
    const s = Math.sin(rad);
    const c = Math.cos(rad);
    const a00 = mat[0];
    const a01 = mat[1];
    const a02 = mat[2];
    const a03 = mat[3];
    const a10 = mat[4];
    const a11 = mat[5];
    const a12 = mat[6];
    const a13 = mat[7];
    mat[0] = a00 * c + a10 * s;
    mat[1] = a01 * c + a11 * s;
    mat[2] = a02 * c + a12 * s;
    mat[3] = a03 * c + a13 * s;
    mat[4] = a10 * c - a00 * s;
    mat[5] = a11 * c - a01 * s;
    mat[6] = a12 * c - a02 * s;
    mat[7] = a13 * c - a03 * s;
};

const mat4 = {
    create,
    perspective,
    translate,
    scale,
    rotateX,
    rotateY,
    rotateZ
};

const degToRad = v => v * Math.PI / 180;

const imageFragmentShader = `
precision mediump float;

uniform sampler2D uTexture;
uniform vec2 uTextureSize;

uniform float uColorOpacity;
uniform mat4 uColorMatrix;
uniform vec4 uColorOffset;

uniform vec4 uOverlayColor;
uniform vec2 uOverlayLeftTop;
uniform vec2 uOverlayRightBottom;

varying vec2 vTexCoord;
varying vec4 vPosition;

void main () {

    vec3 cB = vec3(1.0);

	vec4 cF = texture2D(uTexture, vTexCoord);
	
	vec4 cM = (cF * uColorMatrix) + uColorOffset;

    float r = max(0.0, cM.r * cM.a) + (cB.r * (1.0 - cM.a));
    float g = max(0.0, cM.g * cM.a) + (cB.g * (1.0 - cM.a));
    float b = max(0.0, cM.b * cM.a) + (cB.b * (1.0 - cM.a));

	vec4 color = vec4(r, g, b, cF.a);
	
	// test if falls within
    if ((gl_FragCoord.x < uOverlayLeftTop.x || gl_FragCoord.x > uOverlayRightBottom.x) || 
        (gl_FragCoord.y > uOverlayLeftTop.y || gl_FragCoord.y < uOverlayRightBottom.y)) {
		color *= uOverlayColor;
	}
	
    gl_FragColor = color * uColorOpacity;
}
`;

const imageVertexShader = `
attribute vec4 aPosition;
attribute vec2 aTexCoord;
uniform mat4 uMatrix;

// send to fragment shader
varying vec2 vTexCoord;
varying vec4 vPosition;

void main () {
    vPosition = uMatrix * aPosition;
    gl_Position = vPosition;
    vTexCoord = aTexCoord;
}
`;

const backgroundFragmentShader = `
precision mediump float;

uniform vec2 uViewportSize;
uniform vec3 uColorStart;
uniform vec3 uColorEnd;
uniform vec2 uOverlayLeftTop;
uniform vec2 uOverlayRightBottom;
uniform vec4 uColorCanvasBackground;

void main() {

	float x = gl_FragCoord.x;
	float y = gl_FragCoord.y;

	vec2 center = vec2(.5, .5);
	vec2 st = vec2(x / uViewportSize.x, y / uViewportSize.y);
	float mixValue = distance(st, center) * 1.5; // expand outside view (same as doka--root::after)
	vec3 color = mix(uColorStart, uColorEnd, mixValue);

	if (uColorCanvasBackground[3] == 1.0) {

		float innerLeft = uOverlayLeftTop.x;
		float innerRight = uOverlayRightBottom.x;
		float innerTop = uOverlayRightBottom.y;
		float innerBottom = uOverlayLeftTop.y;

		if (x < innerLeft || x > innerRight || y < innerTop || y > innerBottom) {
			gl_FragColor = vec4(color, 1.0);
			return;
		}

		gl_FragColor = uColorCanvasBackground;
		return;
	}
	
	gl_FragColor = vec4(color, 1.0);
}
`;

const outlineFragmentShader = `
precision mediump float;

uniform vec2 uOverlayLeftTop;
uniform vec2 uOverlayRightBottom;
uniform vec4 uOutlineColor;
uniform float uOutlineWidth;

void main() {

	float x = gl_FragCoord.x;
	float y = gl_FragCoord.y;

	float innerLeft = uOverlayLeftTop.x;
	float innerRight = uOverlayRightBottom.x;
	float innerTop = uOverlayRightBottom.y;
	float innerBottom = uOverlayLeftTop.y;

	float outerLeft = innerLeft - uOutlineWidth;
	float outerRight = innerRight + uOutlineWidth;
	float outerTop = innerTop - uOutlineWidth;
	float outerBottom = innerBottom + uOutlineWidth;
	
	if (x < outerLeft || x >= outerRight || y < outerTop || y >= outerBottom) {
		discard;
	}

	if (x < innerLeft || x >= innerRight || y < innerTop || y >= innerBottom) {
		gl_FragColor = uOutlineColor;
	}
}
`;

const simpleVertexShader = `
attribute vec4 aPosition;
void main() {
	gl_Position = aPosition;
}
`;

const setup = (canvas, imageData, pixelDensity) => {

	let viewSize = {
		width: 0,
		height: 0
	};

	let viewCenter = {
		x: 0,
		y: 0
	};

	let viewAspectRatio = null;

	const resize = (width, height) => {

		// Scale canvas
		canvas.width = width * pixelDensity;
		canvas.height = height * pixelDensity;

		canvas.style.width = `${width}px`;
		canvas.style.height = `${height}px`;
		
		// Update view size
		viewSize.width = width * pixelDensity;
		viewSize.height = height * pixelDensity;

		// Update center
		viewCenter.x = viewSize.width * .5;
		viewCenter.y = viewSize.height * .5;
		
		// Update aspect ratio
		viewAspectRatio = viewSize.width / viewSize.height;

		// Tell WebGL how to convert from clip space to pixels
		gl.viewport(0, 0, gl.canvas.width, gl.canvas.height);
	};

	// fov is fixed
	const FOV = degToRad(30);
	const FOV_TAN_HALF = Math.tan(FOV / 2);

	// gl context attributes 
	const contextAttributes = {
		antialias: false,
		alpha: false
	};

	// get gl drawing context
	const gl = 
		canvas.getContext('webgl', contextAttributes) || 
		canvas.getContext('experimental-webgl', contextAttributes);
	
	// no context received
	if (!gl) return null;

	// toggle gl settings
	gl.enable(gl.BLEND);
	gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
	
	// create background drawing program
	const backgroundProgram = createProgram(gl, simpleVertexShader, backgroundFragmentShader);
	const backgroundColorStartLocation = gl.getUniformLocation(backgroundProgram, 'uColorStart');
	const backgroundColorEndLocation = gl.getUniformLocation(backgroundProgram, 'uColorEnd');
	const backgroundViewportSizeLocation = gl.getUniformLocation(backgroundProgram, 'uViewportSize');
	const backgroundPositionLocation = gl.getAttribLocation(backgroundProgram, 'aPosition');
	const backgroundOverlayLeftTopLocation = gl.getUniformLocation(backgroundProgram, 'uOverlayLeftTop');
	const backgroundOverlayRightBottomLocation = gl.getUniformLocation(backgroundProgram, 'uOverlayRightBottom');
	const backgroundColorCanvasBackground = gl.getUniformLocation(backgroundProgram, 'uColorCanvasBackground');

	const backgroundBuffer = gl.createBuffer();
	const backgroundBufferData = new Float32Array([
		1.0, -1.0, 
		1.0,  1.0, 
		-1.0, -1.0, 
		-1.0,  1.0
	]);

	gl.bindBuffer(gl.ARRAY_BUFFER, backgroundBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, backgroundBufferData, gl.STATIC_DRAW);
	gl.bindBuffer(gl.ARRAY_BUFFER, null);


	// create outline drawing program
	const outlineProgram = createProgram(gl, simpleVertexShader, outlineFragmentShader);
	const outlinePositionLocation = gl.getAttribLocation(outlineProgram, 'aPosition');
	const outlineWidthLocation = gl.getUniformLocation(outlineProgram, 'uOutlineWidth');
	const outlineColorLocation = gl.getUniformLocation(outlineProgram, 'uOutlineColor');
	const outlineOverlayLeftTopLocation = gl.getUniformLocation(outlineProgram, 'uOverlayLeftTop');
	const outlineOverlayRightBottomLocation = gl.getUniformLocation(outlineProgram, 'uOverlayRightBottom');
	
	const outlineBuffer = gl.createBuffer();
	const outlineBufferData = new Float32Array([
		1.0, -1.0, 
		1.0,  1.0, 
		-1.0, -1.0, 
		-1.0,  1.0
	]);

	gl.bindBuffer(gl.ARRAY_BUFFER, outlineBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, outlineBufferData, gl.STATIC_DRAW);
	gl.bindBuffer(gl.ARRAY_BUFFER, null);



	// create default image drawing program
	const imageProgram = createProgram(gl, imageVertexShader, imageFragmentShader);
	
	// tell canvas context to use program
	gl.useProgram(imageProgram);

	// get references to shader props
	const matrixLocation = gl.getUniformLocation(imageProgram, 'uMatrix');
	const textureLocation = gl.getUniformLocation(imageProgram, 'uTexture');
	const textureSizeLocation = gl.getUniformLocation(imageProgram, 'uTextureSize');
	const overlayColorLocation = gl.getUniformLocation(imageProgram, 'uOverlayColor');
	const overlayLeftTopLocation = gl.getUniformLocation(imageProgram, 'uOverlayLeftTop');
	const overlayRightBottomLocation = gl.getUniformLocation(imageProgram, 'uOverlayRightBottom');
	const colorOpacityLocation = gl.getUniformLocation(imageProgram, 'uColorOpacity');
	const colorOffsetLocation = gl.getUniformLocation(imageProgram, 'uColorOffset');
	const colorMatrixLocation = gl.getUniformLocation(imageProgram, 'uColorMatrix');
	const imagePositionsLocation = gl.getAttribLocation(imageProgram, 'aPosition');
	const textureCoordinatesLocation = gl.getAttribLocation(imageProgram, 'aTexCoord');
	
	// set texture 0
	const imageTexture = createTexture(gl, textureLocation, textureSizeLocation, 0, imageData);
	
	const imageWidth = imageData.width * pixelDensity;
	const imageHeight = imageData.height * pixelDensity;

	const l = imageWidth * -.5;
	const t = imageHeight * .5;
	const r = imageWidth * .5;
	const b = imageHeight * -.5;

	const imagePositions = new Float32Array([
		l, t,
		l, b,
		r, t,
		r, b 
	]);

	const texturePositions = new Float32Array([
		0.0, 0.0,
		0.0, 1.0,
		1.0, 0.0,
		1.0, 1.0,
	]);

	const imageCoordinateCount = imagePositions.length / 2;

	const imagePositionsBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, imagePositionsBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, imagePositions, gl.STATIC_DRAW);
	gl.bindBuffer(gl.ARRAY_BUFFER, null);

	const texturePositionsBuffer = gl.createBuffer();
	gl.bindBuffer(gl.ARRAY_BUFFER, texturePositionsBuffer);
	gl.bufferData(gl.ARRAY_BUFFER, texturePositions, gl.STATIC_DRAW);
	gl.bindBuffer(gl.ARRAY_BUFFER, null);

	let _imageWidth = 0;
	let _imageHeight = 0;

	const update = (originX, originY, translateX, translateY, rotateX, rotateY, rotateZ, scale, colorMatrix, colorOpacity, stage, overlay, overlayColor, clearColor, gradientStart, gradientEnd, outlineWidth, outlineColor, canvasBackground) => {
		
		let stageHeight = stage ? stage.height * pixelDensity : viewSize.height;

		_imageWidth = imageData.width * pixelDensity;
		_imageHeight = imageData.height * pixelDensity;

		originX *= pixelDensity;
		originY *= pixelDensity;

		translateX *= pixelDensity;
		translateY *= pixelDensity;

		// move image backwards so it fits stage height
		let z = 
			// 1. we calculate the z offset required to have the 
			//    image height match the view height
			/*        /|
			         / |
			        /  | height / 2
			       /   |
			f / 2 /__z_|
			      \    |
			       \   |
			        \  |
			         \ |
					  \|
			*/
			(_imageHeight / 2) / FOV_TAN_HALF

			// 2. we move it backwards by multiplying with the stage scalar, 
			//    now the height of the image equals the stage height
			* (viewSize.height / stageHeight)
			
			// 3. z has to be negative, therefor multiply by -1
			* -1;
		
		// calculate the image height at the z position, we need this to calculate the factor with which the stage offset should be multiplied
		const zImageHeight = (FOV_TAN_HALF * z) * 2;
		const stageOffsetTranslationFactor = zImageHeight / viewSize.height;

		// scale to actual image size
		z = z / -stageOffsetTranslationFactor;

		const imageCenterX = _imageWidth * .5;
		const imageCenterY = _imageHeight * .5;

		originX = originX - imageCenterX;
		originY = originY - imageCenterY;

		let s = scale;
		
		let x = -(viewCenter.x - imageCenterX) + translateX;
		let y =  (viewCenter.y - imageCenterY) - translateY;

		// apply
		const matrix = mat4.create();
		
		mat4.perspective(matrix, FOV, viewAspectRatio, 1, -z * 2);

		// move image
		mat4.translate(matrix, [x, y, z]);

		// set scale / rotation center point
		mat4.translate(matrix, [originX, -originY, 0]); 

		// resize
		mat4.scale(matrix, [s, s, s]);

		// rotate around center of view not center of image
		mat4.rotateZ(matrix, -rotateZ);

		// reset scale / rotation center point
		mat4.translate(matrix, [-originX, originY, 0]);

		// flip
		mat4.rotateY(matrix, rotateY);
		mat4.rotateX(matrix, rotateX);
		
		// clear
		gl.clearColor(clearColor[0], clearColor[1], clearColor[2], 1);
		gl.clear(gl.COLOR_BUFFER_BIT);

		const overlayX = overlay.x * pixelDensity;
		const overlayY = overlay.y * pixelDensity;
		const overlayWidth = overlay.width * pixelDensity;
		const overlayHeight = overlay.height * pixelDensity;

		const overlayLeft = overlayX;
		const overlayRight = overlayLeft + overlayWidth;
		const overlayTop = viewSize.height - overlayY;
		const overlayBottom = viewSize.height - (overlayY + overlayHeight);


		// tell context to draw background
		// -----------------------------
		gl.useProgram(backgroundProgram);
		gl.uniform3fv(backgroundColorStartLocation, gradientStart);
		gl.uniform3fv(backgroundColorEndLocation, gradientEnd);
		gl.uniform4fv(backgroundColorCanvasBackground, canvasBackground.map((v,i) => i < 3 ? v/255 : v));
		gl.uniform2f(backgroundViewportSizeLocation, viewSize.width, viewSize.height);
		gl.uniform2f(backgroundOverlayLeftTopLocation, overlayLeft, overlayTop);
		gl.uniform2f(backgroundOverlayRightBottomLocation, overlayRight, overlayBottom);
		gl.bindBuffer(gl.ARRAY_BUFFER, backgroundBuffer);
		gl.vertexAttribPointer(backgroundPositionLocation, 2, gl.FLOAT, false, 0, 0);
		gl.enableVertexAttribArray(backgroundPositionLocation);
		gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);

		// tell context to draw image 
		// -----------------------------
		gl.useProgram(imageProgram);

		// set up texture
		gl.bindFramebuffer(gl.FRAMEBUFFER, null);
		gl.bindTexture(gl.TEXTURE_2D, imageTexture);

		// set up buffers
		gl.bindBuffer(gl.ARRAY_BUFFER, imagePositionsBuffer);
		gl.vertexAttribPointer(imagePositionsLocation, 2, gl.FLOAT, false, 0, 0);
		gl.enableVertexAttribArray(imagePositionsLocation);

		gl.bindBuffer(gl.ARRAY_BUFFER, texturePositionsBuffer);
		gl.vertexAttribPointer(textureCoordinatesLocation, 2, gl.FLOAT, false, 0, 0);
		gl.enableVertexAttribArray(textureCoordinatesLocation);

		// update matrix
		gl.uniformMatrix4fv(matrixLocation, false, matrix);

		// the overlay coordinates
		gl.uniform2f(overlayLeftTopLocation, overlayLeft, overlayTop);
		gl.uniform2f(overlayRightBottomLocation, overlayRight, overlayBottom);
		gl.uniform4fv(overlayColorLocation, overlayColor);
		
		// set color matrix values
		gl.uniform1f(colorOpacityLocation, colorOpacity);
		gl.uniform4f(colorOffsetLocation, colorMatrix[4], colorMatrix[9], colorMatrix[14], colorMatrix[19]);
		gl.uniformMatrix4fv(colorMatrixLocation, false, [
			...colorMatrix.slice(0, 4),
			...colorMatrix.slice(5, 9),
			...colorMatrix.slice(10, 14),
			...colorMatrix.slice(15, 19)
		]);

		// redraw image
		gl.drawArrays(gl.TRIANGLE_STRIP, 0, imageCoordinateCount);


		// tell context to draw outline
		// -----------------------------
		gl.useProgram(outlineProgram);
		gl.uniform1f(outlineWidthLocation, outlineWidth);
		gl.uniform4fv(outlineColorLocation, outlineColor);
		gl.uniform2f(outlineOverlayLeftTopLocation, overlayLeft, overlayTop);
		gl.uniform2f(outlineOverlayRightBottomLocation, overlayRight, overlayBottom);
		gl.bindBuffer(gl.ARRAY_BUFFER, outlineBuffer);
		gl.vertexAttribPointer(outlinePositionLocation, 2, gl.FLOAT, false, 0, 0);
		gl.enableVertexAttribArray(outlinePositionLocation);
		gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);

		// done drawing
		api.onupdate(gl);
	};

	const release = () => {
		canvas.width = 1;
		canvas.height = 1;
	};

	// draw function
	const api = {
		release,
		resize,
		update,
		onupdate: () => {}
	};

	return api;

};

const createSpringRect = (config) => {

    let i = 0;
    const rect = {};
    
    const x = spring(config);
    const y = spring(config);
    const width = spring(config);
    const height = spring(config);
    
    x.onupdate = (position) => rect.x = position;
    x.oncomplete = () => i++;
    y.onupdate = (position) => rect.y = position;
    y.oncomplete = () => i++;
    width.onupdate = (position) => rect.width = position;
    width.oncomplete = () => i++;
    height.onupdate = (position) => rect.height = position;
    height.oncomplete = () => i++;

    return {
        interpolate: (ts) => {
            x.interpolate(ts);
            y.interpolate(ts);
            width.interpolate(ts);
            height.interpolate(ts);
        },
        setTarget: (target) => {
            i = 0;
            x.target = target ? target.x : null;
            y.target = target ? target.y : null;
            width.target = target ? target.width : null;
            height.target = target ? target.height : null;
        },
        getRect: () => rect,
        isStable: () => i === 4
    }
};

const createSpringColor = (config) => {

    let i = 0;
    const color = {};
    
    const r = spring(config);
    const g = spring(config);
    const b = spring(config);
    
    r.onupdate = (position) => color.r = position;
    r.oncomplete = () => i++;

    g.onupdate = (position) => color.g = position;
    g.oncomplete = () => i++;

    b.onupdate = (position) => color.b = position;
    b.oncomplete = () => i++;
    
    return {
        interpolate: (ts) => {
            r.interpolate(ts);
            g.interpolate(ts);
            b.interpolate(ts);
        },
        setTarget: (target) => {
            i = 0;
            r.target = target ? target[0] : null;
            g.target = target ? target[1] : null;
            b.target = target ? target[2] : null;
        },
        getColor: () => [color.r, color.g, color.b],
        isStable: () => i === 3
    }
};

const ColorSpring = { stiffness: .25, damping: .25, mass: 2.5 };

const IdentityMatrix = [
    1, 0, 0, 0, 0,
    0, 1, 0, 0, 0,
    0, 0, 1, 0, 0,
    0, 0, 0, 1, 0
];

const imageGL = createView({
    name: 'image-gl',
    ignoreRect: true,
    ignoreRectUpdate: true,
    mixins: {
        apis: [
            'top',
            'left',
            'width',
            'height',
            'xOrigin',
            'yOrigin',
            'xTranslation',
            'yTranslation',
            'xRotation',
            'yRotation',
            'zRotation',
            'scale',
            'overlay',
            'stage',
            'colorMatrix',
            'colorOpacity',
            'overlayOpacity',
            'outlineWidth',
            'isDraft'
        ],
        animations: {
            'xTranslation': imageOverlaySpring,
            'yTranslation': imageOverlaySpring,
            'xOrigin': imageOverlaySpring,
            'yOrigin': imageOverlaySpring,
            'scale': imageOverlaySpring,
            'xRotation': { type: 'spring', stiffness: .25, damping: .25, mass: 2.5 },
            'yRotation': { type: 'spring', stiffness: .25, damping: .25, mass: 2.5 },
            'zRotation': { type: 'spring', stiffness: .25, damping: .25, mass: 2.5 },
            'colorOpacity': { type: 'tween', delay: 150, duration: 750 },
            'overlayOpacity': 'spring',
            'introScale': { type: 'spring', stiffness: .25, damping: .75, mass: 15 },
            'outlineWidth': imageOverlaySpring
        }
    },
    create: ({ root }) => {

        // create canvas and hide, width height will be set automatically
        root.ref.canvas = document.createElement('canvas');
        root.ref.canvas.width = 0;
        root.ref.canvas.height = 0;
        root.appendChild(root.ref.canvas);

        // gl api
        root.ref.gl = null;

        // test if should zoom on initial image load
        root.introScale = 1;
        root.ref.isPreview = root.query('GET_STYLE_LAYOUT_MODE') === 'preview';
        root.ref.shouldZoom = !root.ref.isPreview;
        root.ref.didZoom = false;

        // colors
        root.ref.backgroundColor = null;
        root.ref.backgroundColorSpring = createSpringColor(ColorSpring);
        root.ref.backgroundColorCenter = null;
        root.ref.backgroundColorCenterSpring = createSpringColor(ColorSpring);

        // overlay animator
        root.ref.overlaySpring = createSpringRect(imageOverlaySpring);

        // stage animator
        root.ref.stageSpring = createSpringRect(imageOverlaySpring);

        // outline animator
        root.ref.outlineSpring = spring(imageOverlaySpring);

        // color matrix animator
        root.ref.colorMatrixSpring = [];
        root.ref.colorMatrixStable = true;
        root.ref.colorMatrixStableCount = 0;
        root.ref.colorMatrixPositions = [];
        for (let i =0;i<20;i++) {
            {
                const index = i;
                const s = spring(ColorSpring);
                s.target = IdentityMatrix[index];
                s.onupdate = (position) => {
                    root.ref.colorMatrixPositions[index] = position;
                };
                s.oncomplete = () => {
                    root.ref.colorMatrixStableCount++;
                };
                root.ref.colorMatrixSpring[index] = s;
            }
        }

        // allow dragging
        root.ref.dragger = createDragger(
            root.element,
            () => {
                root.dispatch('CROP_IMAGE_DRAG_GRAB');
            },
            (e, value) => {
                root.dispatch('CROP_IMAGE_DRAG', { value });
            },
            () => {
                root.dispatch('CROP_IMAGE_DRAG_RELEASE');
            },
            {
                cancelOnMultiple: true
            }
        );

        // allow modifications with keybard
        let zoom = 0;
        let rotation = 0;
        root.ref.keyboard = createKeyboard(
            root.element,
            () => {
                zoom = 0;
                rotation = 0;
                return {x:0, y:0};
            },
            {
                up: (offset) => {
                    offset.y -= 20;
                },
                down: (offset) => {
                    offset.y += 20;
                },
                left: (offset) => {
                    offset.x -= 20;
                },
                right: (offset) => {
                    offset.x += 20;
                },
                plus: () => {
                    zoom += .1;
                    root.dispatch('CROP_IMAGE_RESIZE_AMOUNT', { value: zoom });
                    root.dispatch('CROP_IMAGE_RESIZE_RELEASE');
                },
                minus: () => {
                    zoom -= .1;
                    root.dispatch('CROP_IMAGE_RESIZE_AMOUNT', { value: zoom });
                    root.dispatch('CROP_IMAGE_RESIZE_RELEASE');
                },
                left_bracket: () => {
                    rotation -= Math.PI / 128;
                    root.dispatch('CROP_IMAGE_ROTATE_ADJUST', { value: rotation });
                },
                right_bracket: () => {
                    rotation += Math.PI / 128;
                    root.dispatch('CROP_IMAGE_ROTATE_ADJUST', { value: rotation });
                },
                h: () => {
                    root.dispatch('CROP_IMAGE_FLIP_HORIZONTAL');
                },
                l: () => {
                    root.dispatch('CROP_IMAGE_ROTATE_LEFT');
                },
                q: () => {
                    root.dispatch('CROP_RESET');
                },
                r: () => {
                    root.dispatch('CROP_IMAGE_ROTATE_RIGHT');
                },
                v: () => {
                    root.dispatch('CROP_IMAGE_FLIP_VERTICAL');
                },
                z: () => {
                    root.dispatch('CROP_ZOOM');
                }
            },
            (value) => {
                if (!value) return;
                root.dispatch('CROP_IMAGE_DRAG', { value });
            },
            (value) => {
                if (!value) return;
                root.dispatch('CROP_IMAGE_DRAG_RELEASE');
            }
        );

        // load image file
        const file = root.query('GET_FILE');
        const fileURL = URL.createObjectURL(file.data);
        
        const previewImageLoaded = (image) => {

            // draw preview
            const previewSize = scaleImageSize(image, {
                width: root.query('GET_MAX_IMAGE_PREVIEW_WIDTH'),
                height: root.query('GET_MAX_IMAGE_PREVIEW_HEIGHT')
            });

            // draw preview canvas
            const preview = createPreviewImage(image, previewSize.width, previewSize.height, file.orientation);

            // draw thumb canvas
            const pixelRatio = Math.max(1, window.devicePixelRatio * .75);
            const aspectRatio = preview.height / preview.width;
            const size = 96 * pixelRatio;
            const thumbWidth = aspectRatio > 1 ? size : size / aspectRatio;
            const thumbHeight = aspectRatio > 1 ? size * aspectRatio : size;
            const thumb = createPreviewImage(preview, thumbWidth, thumbHeight);

            // store
            const previewData = preview.getContext('2d').getImageData(0, 0, preview.width, preview.height);
            const thumbData = thumb.getContext('2d').getImageData(0, 0, thumb.width, thumb.height);

            // force release of canvas data
            canvasRelease(preview);
            canvasRelease(thumb);
            
            // set up web gl layer
            root.ref.gl = setup(
                root.ref.canvas,
                previewData,
                pixelRatio
            );
            
            const outputSyncTarget = root.query('GET_OUTPUT_CANVAS_SYNC_TARGET');
            if (outputSyncTarget) {
                root.ref.gl.onupdate = () => {
                    const rect = root.ref.overlaySpring.getRect();
                    const ctx = outputSyncTarget.getContext('2d');
                    ctx.drawImage(
                        root.ref.canvas, 
                        rect.x * pixelRatio, rect.y * pixelRatio, rect.width * pixelRatio, rect.height * pixelRatio,
                        0, 0, outputSyncTarget.width, outputSyncTarget.height
                    );
                };
            }
            
            if (!root.ref.gl) {
                root.dispatch('MISSING_WEBGL');
            }
            else {
                root.dispatch('DID_RECEIVE_IMAGE_DATA', { previewData, thumbData });
                root.dispatch('DID_PRESENT_IMAGE');
            }


        };

        const loadPreviewFallback = () => {
            loadImage$2(fileURL).then(previewImageLoaded);
        };

        // if we support scaling using createImageBitmap we use a worker
        if (canCreateImageBitmap(file.data)) {

            // let's scale the image in a worker
            const worker = createWorker(BitmapWorker);
            worker.post(
                {
                    file: file.data
                },
                imageBitmap => {

                    // destroy worker
                    worker.terminate();

                    // no bitmap returned, must be something wrong,
                    // try the oldschool way
                    if (!imageBitmap) {
                        loadPreviewFallback();
                        return;
                    }

                    // yay we got our bitmap, let's continue showing the preview
                    previewImageLoaded(imageBitmap);
                }
            );
        }
        else {
            loadPreviewFallback();
        }

        root.ref.canvasStyle = getComputedStyle(root.ref.canvas);
        root.ref.previousBackgroundColor;

        // compare if should redraw
        root.ref.previousLeft;
        root.ref.previousTop;
        root.ref.previousWidth;
        root.ref.previousHeight;

        // add interaction indicator attribute
        root.element.dataset.showInteractionIndicator = false;

        root.ref.handleFocus = e => {
            if (e.keyCode !== 9) return;
            root.element.dataset.showInteractionIndicator = true;
        };

        root.ref.handleBlur = e => {
            root.element.dataset.showInteractionIndicator = false;
        };

        addEvent(root.element)('keyup', root.ref.handleFocus);
        addEvent(root.element)('blur', root.ref.handleBlur);
    },
    destroy: ({ root }) => {

        if (root.ref.gl) {
            root.ref.gl.release();
            root.ref.gl = null;
        }
        root.ref.dragger.destroy();

        removeEvent(root.element)('keyup', root.ref.handleFocus);
        removeEvent(root.element)('blur', root.ref.handleBlur);
    },
    read: ({ root }) => {

        let bc = root.ref.canvasStyle.backgroundColor;
        let fc = root.ref.canvasStyle.color;
        if (fc === 'transparent' || fc === '') fc = null;
        if (bc === 'transparent' || bc === '') bc = null;
        
        if (bc && bc !== root.ref.previousBackgroundColor) {
            const colors = toRGBColorArray(bc).map(v => v/255);
            const average = (colors[0] + colors[1] + colors[2]) / 3;
            root.ref.backgroundColor = colors;
            root.ref.backgroundColorCenter = colors.map(color => average > .5 ? color - .15 : color + .15);
            root.ref.previousBackgroundColor = bc;
        }

        if (fc && fc !== root.ref.previousOutlineColor) {
            root.ref.outlineColor = toRGBColorArray(fc).map(v => v/255).concat(1.0);
            root.ref.previousOutlineColor = fc;
        }

    },
    write: createRoute({
        SHOW_VIEW: ({ root, action }) => {
            if (action.id === 'crop') {
                root.ref.dragger.enable();
                root.element.setAttribute('tabindex', '0');
            }
            else {
                root.ref.dragger.disable();
                root.element.removeAttribute('tabindex');
            }
        }
    },
    ({ root, props, actions, timestamp }) => {

        // the gl context has to be created for drawing
        if (!root.ref.gl) return;

        // width and height are required for drawing
        if (!props.width || !props.height) return;

        const {
            gl, 
            previousWidth, previousHeight, 
            shouldZoom, 
            stageSpring, 
            overlaySpring, 
            backgroundColorSpring, 
            backgroundColorCenterSpring
        } = root.ref;
        
        if (props.width !== previousWidth || props.height !== previousHeight) {
            root.ref.gl.resize(props.width, props.height);
            root.ref.previousWidth = props.width;
            root.ref.previousHeight = props.height;
        }

        if (props.left !== root.ref.previousLeft || props.top !== root.ref.previousTop) {
            root.ref.canvas.style.transform = `translate(${-props.left}px, ${-props.top}px)`;
            root.ref.previousLeft = props.left;
            root.ref.previousTop = props.top;
        }
        
        if (shouldZoom && !root.ref.didZoom) {
            root.introScale = null;
            root.introScale = 1.15;
            root.introScale = 1;
            root.ref.didZoom = true;
        }

        // animate background color
        backgroundColorSpring.setTarget(root.ref.backgroundColor);
        backgroundColorSpring.interpolate(timestamp);
        const backgroundColorStable = backgroundColorSpring.isStable();

        // animate background center color
        backgroundColorCenterSpring.setTarget(root.ref.backgroundColorCenter);
        backgroundColorCenterSpring.interpolate(timestamp);
        const backgroundColorCenterStable = backgroundColorCenterSpring.isStable();

        // animate color matrix
        root.ref.colorMatrixStableCount = 0;
        const colorMatrixTarget = props.colorMatrix || IdentityMatrix;
        const colorMatrixCurrent = root.ref.colorMatrixSpring.map((s, i) => {
            s.target = colorMatrixTarget[i];
            s.interpolate(timestamp);
            return root.ref.colorMatrixPositions[i];
        });
        const colorMatrixStable = root.ref.colorMatrixStableCount === 20;

        // animate overlay
        if (props.isDraft) overlaySpring.setTarget(null);
        overlaySpring.setTarget(props.overlay);
        overlaySpring.interpolate(timestamp);
        const overlayStable = overlaySpring.isStable();

        // animate overlay
        if (props.isDraft) stageSpring.setTarget(null);
        stageSpring.setTarget(props.stage);
        stageSpring.interpolate(timestamp);
        const stageStable = stageSpring.isStable();

        gl.update(
            root.xOrigin,
            root.yOrigin,
            root.xTranslation + props.left,
            root.yTranslation + props.top,
            root.xRotation,
            root.yRotation,
            root.zRotation,
            root.scale * root.introScale,
            colorMatrixCurrent,
            root.ref.isPreview ? 1.0 : root.colorOpacity,
            stageSpring.getRect(),
            overlaySpring.getRect(),
            [
                1.0,
                1.0,
                1.0,
                1.0 - root.overlayOpacity
            ],
            backgroundColorSpring.getColor(),
            backgroundColorCenterSpring.getColor(),
            backgroundColorSpring.getColor(),
            root.outlineWidth,
            root.ref.outlineColor,
            root.query('GET_BACKGROUND_COLOR')
        );

        return overlayStable && stageStable && colorMatrixStable && backgroundColorStable && backgroundColorCenterStable;
    })

});

const image = createView({
    name: 'image',
    ignoreRect: true,
    mixins: {
        apis: [
            'offsetTop'
        ]
    },
    create: ({ root, props }) => {

        // create image preview
        root.ref.imageGL = root.appendChildView(
            root.createChildView(imageGL)
        );

        //#if !_EXCLUDE_MARKUP_UTIL
        if (/markup/.test(root.query('GET_UTILS'))) {
            root.ref.markup = root.appendChildView(
                root.createChildView(
                    imageMarkup, {
                        id: props.id,
                        opacity: 0,
                        onSelect: (id) => {
                            root.dispatch('MARKUP_SELECT', { id });
                        },
                        onDrag: (id, origin, offset, size, scale) => {
                            root.dispatch('MARKUP_ELEMENT_DRAG', { id, origin, offset, size, scale });
                        },
                        onResize: (id, corner, origin, offset, size) => {
                            root.dispatch('MARKUP_ELEMENT_RESIZE', { id, corner, origin, offset, size });
                        },
                        onUpdate: (style, value) => {
                            root.dispatch('MARKUP_UPDATE', { style, value });
                        },
                        isMarkupUtil: (element) => {
                            let el = element;
                            do {
                                if (el.className === 'doka--markup-tools') {
                                    return true;
                                }
                            }
                            // eslint-disable-next-line no-cond-assign
                            while(el = el.parentNode);
                            return false;
                        }
                    }
                )
            );
        }
        //#endif

        root.ref.isModal = /modal/.test(root.query('GET_STYLE_LAYOUT_MODE'));
    },
    write: createRoute({
        DID_PRESENT_IMAGE: ({ root }) => {
            root.ref.imageGL.colorOpacity = 1;
        }
    },
    ({ root, props, timestamp }) => {

        const { imageGL: imageGL$$1 } = root.ref;
        let { markup } = root.ref;
        
        const view = root.query('GET_CROP', props.id, timestamp);
        if (!view) return;

        const {
            isDraft,
            cropRect,
            cropStatus,
            origin,
            translation,
            translationBand,
            scale,
            scaleBand,
            rotation,
            rotationBand,
            flip,
            colorMatrix
        } = view;

        // get stage info
        const editorRootRect = root.query('GET_ROOT');
        const editorStageRect = root.query('GET_STAGE');
        const stageOffsetX = editorStageRect.x;
        const stageOffsetY = editorStageRect.y;

        // update image
        if (isDraft) {
            imageGL$$1.scale = null;
            imageGL$$1.zRotation = null;
            imageGL$$1.xTranslation = null;
            imageGL$$1.yTranslation = null;
            imageGL$$1.xOrigin = null;
            imageGL$$1.yOrigin = null;
        }

        imageGL$$1.colorMatrix = colorMatrix;

        const isCropView = root.query('IS_ACTIVE_VIEW', 'crop');
        const isMarkupView = root.query('IS_ACTIVE_VIEW', 'markup');
        const overlayOpacity = isCropView ? .75 : .95;
        let rect = {...cropRect};
        let imagePreviewScalar = 1;
        let imageOutlineEdgeWidth = isCropView ? 1 : 5;

        // draw resize view
        if (root.query('IS_ACTIVE_VIEW', 'resize')) {

            let targetWidth = cropStatus.image.width;
            let targetHeight = cropStatus.image.height;

            if (targetWidth === null && targetHeight === null) {
                imagePreviewScalar = cropStatus.crop.width / cropRect.width;
            }
            else if (targetWidth === null) {
                imagePreviewScalar = targetHeight / cropRect.height;
            }
            else {
                imagePreviewScalar = targetWidth / cropRect.width;
            }

            imagePreviewScalar /= window.devicePixelRatio;

            const previewWidth = cropRect.width * imagePreviewScalar;
            const previewHeight = cropRect.height * imagePreviewScalar;
            rect.x = rect.x + ((cropRect.width * .5) - (previewWidth * .5));
            rect.y = rect.y + ((cropRect.height * .5) - (previewHeight * .5));
            rect.width = previewWidth;
            rect.height = previewHeight;
        }

        const rootOffsetLeft = root.ref.isModal ? 0 : editorRootRect.left;
        const rootOffsetTop = root.ref.isModal ? 0 : editorRootRect.top;
        const rootExtraWidth = root.ref.isModal ? 0 : editorRootRect.width - root.rect.element.width;
        const rootExtraHeight = root.ref.isModal ? 0 : editorRootRect.height - root.rect.element.height - props.offsetTop;
        
        const imageScalar = (scale + scaleBand) * imagePreviewScalar;

        imageGL$$1.isDraft = isDraft;

        imageGL$$1.overlayOpacity = overlayOpacity;
        
        imageGL$$1.xOrigin = origin.x;
        imageGL$$1.yOrigin = origin.y;

        imageGL$$1.xTranslation = translation.x + translationBand.x + stageOffsetX;
        imageGL$$1.yTranslation = translation.y + translationBand.y + stageOffsetY;

        imageGL$$1.left = rootOffsetLeft;
        imageGL$$1.top = rootOffsetTop + props.offsetTop;
        imageGL$$1.width = root.rect.element.width + rootExtraWidth;
        imageGL$$1.height = root.rect.element.height + rootExtraHeight + props.offsetTop;

        imageGL$$1.scale = imageScalar;

        imageGL$$1.xRotation = flip.vertical ? Math.PI : 0;
        imageGL$$1.yRotation = flip.horizontal ? Math.PI : 0;
        imageGL$$1.zRotation = (rotation.main + rotation.sub) + rotationBand;
        
        imageGL$$1.stage = {
            x: editorStageRect.x + rootOffsetLeft,
            y: editorStageRect.y + rootOffsetTop + props.offsetTop,
            width: editorStageRect.width,
            height: editorStageRect.height
        };

        imageGL$$1.overlay = {
            x: rect.x + stageOffsetX + rootOffsetLeft,
            y: rect.y + stageOffsetY + rootOffsetTop + props.offsetTop,
            width: rect.width,
            height: rect.height
        };

        imageGL$$1.outlineWidth = imageOutlineEdgeWidth;

        //#if !_EXCLUDE_MARKUP_UTIL
        if (!markup) return;
        
        if (isDraft) {
            markup.translateX = null;
            markup.translateY = null;
            markup.markupX = null;
            markup.markupY = null;
            markup.markupWidth = null;
            markup.markupHeight = null;
        }

        markup.opacity = isCropView ? .3 : 1;
        markup.stageOffsetX = stageOffsetX;
        markup.stageOffsetY = stageOffsetY;
        markup.markupX = rect.x + stageOffsetX;
        markup.markupY = rect.y + stageOffsetY;
        markup.markupWidth = rect.width;
        markup.markupHeight = rect.height;
        markup.allowInteraction = isMarkupView;
        //#endif
    })

});

const createGroup = ( name = 'group', styles = ['opacity'],  animations = {}) => createView({
    ignoreRect: true,
    name,
    mixins: {
        styles: ['opacity', ...styles],
        animations: {
            'opacity': {
                type: 'spring',
                stiffness: .25,
                damping: .5,
                mass: 5
            },
            ...animations
        }
    },
    create: ({ root, props }) => {
        (props.controls || []).map(control => {
            const view = root.createChildView(control.view, control);
            if (control.didCreateView) {
                control.didCreateView(view);
            }
            root.appendChildView(view);
        });
        if (props.element) {
            root.element.appendChild(props.element);
        }
    }
});

const list = createView({
    ignoreRect: true,
    tag: 'div',
    name: 'dropdown-list',
    mixins: {
        styles: [
            'translateY',
            'opacity'
        ],
        apis: [
            'selectedValue',
            'options',
            'onSelect'
        ],
        animations: {
            'translateY': 'spring',
            'opacity': { type: 'tween', duration: 250 }
        }
    },
    create: ({ root, props }) => {

        root.element.setAttribute('role', 'list');

        root.ref.handleClick = () => props.action && props.action();
        root.element.addEventListener('click', root.ref.handleClick);

        root.ref.activeOptions = null;
        root.ref.activeSelectedValue;
    },
    write: ({ root, props }) => {

        if (props.options !== root.ref.activeOptions) {

            root.ref.activeOptions = props.options;

            root.childViews.forEach(view => root.removeChildView(view));

            props.options.map(option => {
                const view = root.createChildView(button, {
                    ...option,
                    action: () => props.onSelect(option.value)// root.dispatch(props.optionAction, { value: option.value })
                });
                return root.appendChildView(view);
            });

        }

        if (props.selectedValue !== root.ref.activeSelectedValue) {

            root.ref.activeSelectedValue = props.selectedValue;
            
            const actionIndex = props.options.findIndex(option => {
                if (typeof option.value === 'object' && props.selectedValue) {
                    return JSON.stringify(option.value) === JSON.stringify(props.selectedValue);
                }
                return option.value === props.selectedValue
            });

            root.childViews.forEach((view, index) => {
                view.element.setAttribute('aria-selected', index === actionIndex);
            });

        }
        
    },
    destroy: ({ root }) => {
        root.element.removeEventListener('click', root.ref.handleClick);
    }
});

const dropdown = createView({
    ignoreRect: true,
    tag: 'div',
    name: 'dropdown',
    mixins: {
        styles: [
            'opacity'
        ],
        animations: {
            'opacity': 'spring'
        },
        apis: [
            'direction',
            'selectedValue',
            'options',
            'onSelect'
        ]
    },
    create: ({ root, props }) => {

        // init
        root.ref.open = false;

        const toggle = (state) => {
            root.ref.open = state;
            root.dispatch('KICK');
        };

        root.ref.button = root.appendChildView(root.createChildView(button, {
            ...props,
            action: () => {
                toggle(!root.ref.open);
            }
        }));
        
        root.ref.list = root.appendChildView(root.createChildView(list, {
            ...props,
            opacity: 0,
            action: () => {
                toggle(false);
            }
        }));

        // catch outside clicks
        root.ref.handleBodyClick = e => {
            if (!root.element.contains(e.target)) {
                toggle(false);
            }
        };

        root.element.addEventListener('focusin', e => {
            if (e.target === root.ref.button.element) return;
            toggle(true);
        });

        root.element.addEventListener('focusout', e => {
            if (root.element.contains(e.relatedTarget)) return;
            toggle(false);
        });

        document.body.addEventListener('click', root.ref.handleBodyClick);

    },
    destroy: ({ root }) => {
        document.body.removeEventListener('click', root.ref.handleBodyClick);
    },
    write: ({ root, props }) => {
        root.ref.list.opacity = root.ref.open ? 1 : 0;
        root.ref.list.selectedValue = props.selectedValue;
        root.ref.list.options = props.options;
        if (props.direction === 'up') {
            const height = root.ref.list.rect.element.height;
            root.ref.list.translateY = (root.ref.open ? -(height + 5) : -height) - root.rect.element.height;
        }
        else {
            root.ref.list.translateY = root.ref.open ? 0 : -5;
        }
    }
});

//#if !_EXCLUDE_CROP_UTIL
const MAGIC = 312;

const createDiv = (name, create) => createView({
    name,
    ignoreRect: true,
    create
});

const cropRotatorLine = createView({
    name: 'crop-rotator-line',
    ignoreRect: true,
    ignoreRectUpdate: true,
    mixins: {
        styles: [
            'translateX'
        ],
        animations: {
            'translateX': 'spring'
        }
    },
    create: ({ root }) => {
        
        const degreesOffset = -90;
        const degreesRange = 180;
        const degreesStep = 2;
        const spacing = 2;
        const stepping = (degreesRange - (spacing * 2)) / degreesRange;
        
        let svg = `<svg viewBox="-90 -5 180 10" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">`;
        for (let i=0; i<=degreesRange; i+=degreesStep ) {
            
            const x = spacing + (degreesOffset + (i * stepping));
            const y = 0;
            const r = i % 10 === 0 ? .5 : .2;

            svg += `<circle fill="currentColor" cx="${x}" cy="${y}" r="${r}"/>`;

            if (i % 10 === 0) {
                const offset = x < 0 ? -2.25 : x === 0 ? -.75 : -1.5;
                svg += `<text fill="currentColor" x="${x + offset}" y="${y + 3.5}">${degreesOffset + i}&deg;</text>`;
            }
            
        }
        svg += '</svg>';
        root.element.innerHTML = svg;

    }
});

const cropRotator = createView({
    name: 'crop-rotator',
    ignoreRect: true,
    mixins: {
        styles: [
            'opacity',
            'translateY'
        ],
        animations: {
            'opacity': { type: 'spring', damping: .5, mass: 5 },
            'translateY': 'spring'
        },
        apis: [
            'rotation',
            'animate',
            'setAllowInteraction'
        ]
    },
    create: ({ root, props }) => {

        // allow tabbing to root element
        root.element.setAttribute('tabindex', 0);

        // center
        const btnCenter = document.createElement('button');
        btnCenter.innerHTML = `<span>${root.query('GET_LABEL_BUTTON_CROP_ROTATE_CENTER')}</span>`,
        btnCenter.className = 'doka--crop-rotator-center';
        btnCenter.addEventListener('click', () => {
            root.dispatch('CROP_IMAGE_ROTATE_CENTER');
        });
        root.appendChild(btnCenter);

        // mask
        let line = null;
        root.appendChildView(
            root.createChildView(
                createDiv('crop-rotator-line-mask', ({ root, props }) => {
                    line = root.appendChildView(
                        root.createChildView(cropRotatorLine, {
                            translateX: Math.round(props.rotation * MAGIC)
                        })
                    );
                }), props
            )
        );
        root.ref.line = line;

        // drag area
        const bar = document.createElement('div');
        bar.className = 'doka--crop-rotator-bar';
        root.appendChild(bar);

        // rotation range
        const range = Math.PI / 4;

        // current offset
        let rotationOffset = 0;

        // entire thing is a dragger
        root.ref.dragger = createDragger(
            bar,
            () => {
                rotationOffset = line.translateX / MAGIC;
                root.dispatch('CROP_IMAGE_ROTATE_GRAB');
            },
            (e, offset) => {

                const additionalRotation = (offset.x / root.rect.element.width) * (Math.PI / 2);

                const value = limit(
                    rotationOffset + additionalRotation,
                    -range,
                    range
                );

                root.dispatch('CROP_IMAGE_ROTATE', { value: -value });
            },
            () => {
                root.dispatch('CROP_IMAGE_ROTATE_RELEASE');
            },
            {
                stopPropagation: true
            }
        );

        props.setAllowInteraction = (enabled) => {
            if (enabled) {
                root.ref.dragger.enable();
            }
            else {
                root.ref.dragger.disable();
            }
        };

        root.ref.keyboard = createKeyboard(
            root.element,
            () => {
                rotationOffset = 0;
            },
            {
                left: () => {
                    rotationOffset += Math.PI / 128;
                    root.dispatch('CROP_IMAGE_ROTATE_ADJUST', { value: rotationOffset });
                },
                right: () => {
                    rotationOffset -= Math.PI / 128;
                    root.dispatch('CROP_IMAGE_ROTATE_ADJUST', { value: rotationOffset });
                }
            },
            () => {},
            () => {}
        );

        root.ref.prevRotation;
    },
    destroy:({ root }) => {
        root.ref.dragger.destroy();
        root.ref.keyboard.destroy();
    },
    write: ({ root, props, timestamp }) => {

        const { animate, rotation } = props;

        if (root.ref.prevRotation === rotation) return;

        root.ref.prevRotation = rotation;

        if (!animate && rotation !== 0) {
            root.ref.line.translateX = null;
        }

        let rotationBand = 0;
        const view = root.query('GET_CROP', props.id, timestamp);
        if (view && view.interaction && view.interaction.rotation) {
            const DIST = .025;
            const s = splitRotation(view.interaction.rotation).sub - rotation;
            rotationBand = DIST * Math.sign(s) * Math.log10(1 + (Math.abs(s) / DIST));
        }

        root.ref.line.translateX = Math.round((-rotation - rotationBand) * MAGIC);
    }
});
//#endif

const corners = ['nw', 'ne', 'se', 'sw'];
const getOppositeCorner = corner => corners[(corners.indexOf(corner) + 2) % corners.length];

const edges = ['n', 'e', 's', 'w'];
const getOppositeEdge = edge => edges[(edges.indexOf(edge) + 2) % edges.length];

const autoPrecision = isBrowser() && window.devicePixelRatio === 1 ? v => Math.round(v) : v => v;

//#if !_EXCLUDE_CROP_UTIL
const line = createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    name: 'crop-rect-focal-line',
    mixins: {
        styles: [
            'translateX',
            'translateY',
            'scaleX',
            'scaleY',
            'opacity'
        ],
        animations: {
            'translateX': 'spring',
            'translateY': 'spring',
            'scaleX': 'spring',
            'scaleY': 'spring',
            'opacity': 'spring'
        }
    }
});

const createEdge = (name) => createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    tag: 'div', // this is a <div> as <button> on Firefox because of a bug does not support inner elements outside of it's rectangle
    name: `crop-rect-edge-${name}`,
    mixins: {
        styles: [
            'translateX',
            'translateY',
            'scaleX',
            'scaleY'
        ],
        apis: [
            'setAllowInteraction'
        ]
    },
    create: ({ root, props }) => {
        root.element.classList.add('doka--crop-rect-edge');
        root.element.setAttribute('tabindex', 0);
        root.element.setAttribute('role', 'button');
        
        const origin = name;
        const anchor = getOppositeEdge(name);

        root.ref.dragger = createDragger(
            root.element,
            () => {
                root.dispatch('CROP_RECT_DRAG_GRAB');
            },
            (e, offset) => root.dispatch('CROP_RECT_EDGE_DRAG', {
                offset,
                origin,
                anchor
            }),
            () => root.dispatch('CROP_RECT_DRAG_RELEASE'),
            {
                stopPropagation: true,
                cancelOnMultiple: true
            }
        );

        props.setAllowInteraction = (enabled) => {
            if (enabled) {
                root.ref.dragger.enable();
            }
            else {
                root.ref.dragger.disable();
            }
        };

        root.ref.keyboard = createKeyboard(
            root.element,
            () => ({ x:0, y:0 }),
            {
                up: (offset) => {
                    offset.y -= 20;
                },
                down: (offset) => {
                    offset.y += 20;
                },
                left: (offset) => {
                    offset.x -= 20;
                },
                right: (offset) => {
                    offset.x += 20;
                }
            },
            (offset) => {
                root.dispatch('CROP_RECT_DRAG_GRAB');

                root.dispatch('CROP_RECT_EDGE_DRAG', {
                    offset,
                    origin,
                    anchor
                });
            },
            () => {
                root.dispatch('CROP_RECT_DRAG_RELEASE');
            }
        );
        
    },
    destroy:({ root }) => {
        root.ref.keyboard.destroy();
        root.ref.dragger.destroy();
    }
});

const createCorner = (name, opacityDelay, cornerDelay) => createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    tag: 'div', // this is a <div> as <button> on Firefox because of a bug does not support inner elements outside of it's rectangle
    name: `crop-rect-corner-${name}`,
    mixins: {
        styles: [
            'translateX',
            'translateY',
            'scaleX',
            'scaleY'
        ],
        animations: {
            'translateX': imageOverlaySpring,
            'translateY': imageOverlaySpring,
            'scaleX': { type: 'spring', delay: cornerDelay },
            'scaleY': { type: 'spring', delay: cornerDelay },
            'opacity': { type: 'spring', delay: opacityDelay }
        },
        apis: ['setAllowInteraction']
    },
    create: ({ root, props }) => {
        root.element.classList.add('doka--crop-rect-corner');
        root.element.setAttribute('role', 'button');
        root.element.setAttribute('tabindex', -1);

        const origin = name;
        const anchor = getOppositeCorner(name);

        root.ref.dragger = createDragger(
            root.element,
            () => {
                root.dispatch('CROP_RECT_DRAG_GRAB');
            },
            (e, offset) => {
                root.dispatch('CROP_RECT_CORNER_DRAG', {
                    offset,
                    origin,
                    anchor
                });
            },
            () => {
                root.dispatch('CROP_RECT_DRAG_RELEASE');
            },
            {
                stopPropagation: true,
                cancelOnMultiple: true
            }
        );

        props.setAllowInteraction = (enabled) => {
            if (enabled) {
                root.ref.dragger.enable();
            }
            else {
                root.ref.dragger.disable();
            }
        };
    },
    destroy:({ root }) => {
        root.ref.dragger.destroy();
    }
});

const cropRect = createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    name: 'crop-rect',
    mixins: {
        apis: [
            'rectangle', 
            'draft', 
            'rotating',
            'enabled'
        ]
    },
    create: ({ root }) => {

        root.ref.wasRotating = false;

        // corners
        const cornerAnimationDelay = 250;
        const cornerAnimationInterval = 10;
        corners.forEach((name, i) => {
            const delayInterval = i * cornerAnimationInterval;
            const opacityDelay = cornerAnimationDelay + delayInterval + 50;
            const cornerDelay = cornerAnimationDelay + delayInterval;
            root.ref[name] = root.appendChildView(
                root.createChildView(
                    createCorner(name, opacityDelay, cornerDelay),
                    {
                        opacity: 0,
                        scaleX: .5,
                        scaleY: .5
                    }
                )
            );
        });

        // edges
        edges.forEach(name => {
            root.ref[name] = root.appendChildView(
                root.createChildView(
                    createEdge(name)
                )
            );
        });

        // lines
        root.ref.lines = [];
        for (let i=0;i<10;i++) {
            root.ref.lines.push(root.appendChildView(root.createChildView(line, {opacity:0})));
        }

        // showing/hiding state
        root.ref.animationDir = null;


        // used to test for changes
        root.ref.previousRotating;
        root.ref.previousRect = {};
        root.ref.previousEnabled;
        root.ref.previousDraft;
    },
    write: ({ root, props }) => {

        const { rectangle, draft, rotating, enabled } = props;

        if (!rectangle) return;

        // same rectangle as before
        if (
            rectEqualsRect(rectangle, root.ref.previousRect) &&
            rotating === root.ref.previousRotating &&
            enabled === root.ref.previousEnabled && 
            draft === root.ref.previousDraft
        ) return;

        // remember for next loop
        root.ref.previousRect = rectangle;
        root.ref.previousRotating = rotating;
        root.ref.previousEnabled = enabled;
        root.ref.previousDraft = draft;

        const { n, e, s, w, nw, ne, se, sw, lines, animationDir } = root.ref;

        const left = rectangle.x;
        const top = rectangle.y;
        const right = rectangle.x + rectangle.width;
        const bottom = rectangle.y + rectangle.height;
        const height = bottom - top;
        const width = right - left;

        const size = Math.min(width, height);
        root.element.dataset.indicatorSize = size < 80 ? 'none' : 'default';
        
        edges.forEach(name => root.ref[name].setAllowInteraction(enabled));
        corners.forEach(name => root.ref[name].setAllowInteraction(enabled));
        
        const isActive = root.query('IS_ACTIVE_VIEW', 'crop');
        if (isActive && animationDir !== 'in') {
            root.ref.animationDir = 'in';
            corners.map(name => root.ref[name]).forEach(view => {
                view.opacity = 1;
                view.scaleX = 1;
                view.scaleY = 1;
            });
        }
        else if (!isActive && animationDir !== 'out') {
            root.ref.animationDir = 'out';
            corners.map(name => root.ref[name]).forEach(view => {
                view.opacity = 0;
                view.scaleX = .5;
                view.scaleY = .5;
            });
        }
        
        transformTranslate(draft, nw, left, top);
        transformTranslate(draft, ne, right, top);
        transformTranslate(draft, se, right, bottom);
        transformTranslate(draft, sw, left, bottom);

        transformTranslateScale(draft, n, left, top, width / 100, 1);
        transformTranslateScale(draft, e, right, top, 1, height / 100);
        transformTranslateScale(draft, s, left, bottom, width / 100, 1);
        transformTranslateScale(draft, w, left, top, 1, height / 100);

        // need to rotate line 2 and line 3
        if (rotating) {

            root.ref.wasRotating = true;

            const horizontal = lines.slice(0, 5);
            let offset = 1 / horizontal.length;
            horizontal.forEach((line, i) => {
                const y = offset + (i * offset);
                transformTranslateScale(draft, line, left, top + (height * y), width / 100, .01);
                line.opacity = .5;
            });

            const vertical = lines.slice(5);
            offset = 1 / vertical.length;
            vertical.forEach((line, i) => {
                const x = offset + (i * offset);
                transformTranslateScale(draft, line, left + (width * x), top, .01, height / 100);
                line.opacity = .5;
            });

        }
        else if (draft) {

            root.ref.wasRotating = false;

            const h1 = lines[0];
            const h2 = lines[1];
            const v1 = lines[2];
            const v2 = lines[3];

            transformTranslateScale(draft, h1, left, top + (height * .333), width / 100, .01);
            transformTranslateScale(draft, h2, left, top + (height * .666), width / 100, .01);
            transformTranslateScale(draft, v1, left + (width * .333), top, .01, height / 100);
            transformTranslateScale(draft, v2, left + (width * .666), top, .01, height / 100);

            h1.opacity = .5;
            h2.opacity = .5;
            v1.opacity = .5;
            v2.opacity = .5;
        }
        else {

            const h1 = lines[0];
            const h2 = lines[1];
            const v1 = lines[2];
            const v2 = lines[3];
            
            if (!root.ref.wasRotating && h1.opacity > 0) {
                transformTranslateScale(draft, h1, left, top + (height * .333), width / 100, .01);
                transformTranslateScale(draft, h2, left, top + (height * .666), width / 100, .01);
                transformTranslateScale(draft, v1, left + (width * .333), top, .01, height / 100);
                transformTranslateScale(draft, v2, left + (width * .666), top, .01, height / 100);
            }

            lines.forEach(line => line.opacity = 0);
        }
        
    }
});

const transformTranslateScale = (draft, view, x, y, width, height) => {
    if (draft) {
        view.translateX = null;
        view.translateY = null;
        view.scaleX = null;
        view.scaleY = null;
    }
    view.translateX = autoPrecision(x);
    view.translateY = autoPrecision(y);
    view.scaleX = width;
    view.scaleY = height;
};

const transformTranslate = (draft, view, x, y) => {
    if (draft) {
        view.translateX = null;
        view.translateY = null;
    }
    view.translateX = autoPrecision(x);
    view.translateY = autoPrecision(y);
};
//#endif

const setInnerHTML = (node, html) => {
    if (/svg/.test(node.namespaceURI) && !('innerHTML' in node)) {
        const container = document.createElement('div');
        container.innerHTML = '<svg>' + html + '</svg>';
        const svg = container.firstChild;
        while (svg.firstChild) {
          node.appendChild(svg.firstChild);
        }
    }
    else {
        node.innerHTML = html;
    }
};

//#if !_EXCLUDE_CROP_UTIL
const cropMask = createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    name: 'crop-mask',
    tag: 'svg',
    mixins: {
        styles: [
            'opacity', 
            'translateX',
            'translateY'
        ],
        animations: {
            'scale': imageOverlaySpring,
            'maskWidth': imageOverlaySpring,
            'maskHeight': imageOverlaySpring,
            'translateX': imageOverlaySpring,
            'translateY': imageOverlaySpring,
            'opacity': { type: 'tween', delay: 0, duration: 1000 },
        },
        apis: [
            'rectangle',
            'animate',
            'maskWidth',
            'maskHeight',
            'scale'
        ]
    },
    create: ({ root }) => {
        root.ref.writer = null;
    },
    write: ({ root }) => {
        const writer = root.query('GET_CROP_MASK');
        if (writer !== root.ref.writer) {
            root.ref.writer = writer;
            root.ref.writerFn = writer ? writer(root.element, setInnerHTML) : null;
            if (!root.ref.writer) setInnerHTML(root.element, '');
        }
    },
    didWriteView: ({ root, props }) => {
        
        const { maskWidth, maskHeight, scale } = props;

        if (!root.ref.writer || !maskWidth || !maskHeight) return;

        root.element.setAttribute('width', autoPrecision(maskWidth));
        root.element.setAttribute('height', autoPrecision(maskHeight));

        if (!root.ref.writerFn) return;

        const inset = root.query('GET_CROP_MASK_INSET');

        root.ref.writerFn(
            {
                x: scale * inset,
                y: scale * inset,
                width: maskWidth - (scale * inset * 2),
                height: maskHeight - (scale * inset * 2)
            },
            {
                width: maskWidth,
                height: maskHeight
            }
        );

    }

});
//#endif

const updateText$1 = (node, value) => {
    let textNode = node.childNodes[0];
    if (!textNode) {
        textNode = document.createTextNode(value);
        node.appendChild(textNode);
    } else if (value !== textNode.nodeValue) {
        textNode.nodeValue = value;
    }
};

//#if !_EXCLUDE_CROP_UTIL
const sizeSpring = {
    type: 'spring',
    stiffness: .25,
    damping: .1,
    mass: 1
};

const cropSize = createView({
    ignoreRect: true,
    name: 'crop-size',
    mixins: {
        styles: [
            'translateX',
            'translateY',
            'opacity'
        ],
        animations: {
            'translateX': 'spring',
            'translateY': 'spring',
            'opacity': 'spring',
            'sizeWidth': sizeSpring,
            'sizeHeight': sizeSpring
        },
        apis: [
            'sizeWidth',
            'sizeHeight'
        ],
        listeners: true
    },
    create: ({ root }) => {

        // resize percentage
        const resizePercentage = createElement('span');
        resizePercentage.className = 'doka--crop-size-info doka--crop-resize-percentage';
        root.ref.resizePercentage = resizePercentage;
        root.appendChild(resizePercentage);

        // output size
        const outputInfo = createElement('span');
        outputInfo.className = 'doka--crop-size-info';
        const outputTimes = createElement('span');
        outputTimes.className = 'doka--crop-size-multiply';
        outputTimes.textContent = '×';
        const outputWidth = createElement('span');
        const outputHeight = createElement('span');
        root.ref.outputWidth = outputWidth;
        root.ref.outputHeight = outputHeight;
        outputInfo.appendChild(outputWidth);
        outputInfo.appendChild(outputTimes);
        outputInfo.appendChild(outputHeight);
        root.appendChild(outputInfo);

        // values 
        root.ref.previousValues = {
            width: 0,
            height: 0,
            percentage: 0
        };

    },
    write: ({ root, props, timestamp }) => {

        if (root.opacity <= 0) return;

        const view = root.query('GET_CROP', props.id, timestamp);
        if (!view) return;

        const { cropStatus, isDraft } = view;
        const { outputWidth, outputHeight, resizePercentage, previousValues } = root.ref;
        
        const { image,
            crop,
            currentWidth,
            currentHeight
         } = cropStatus;

        const currentPercentage = image.width ? Math.round(100 * (image.width / crop.width)) : 0;

         if (isDraft) {
             root.sizeWidth = null;
             root.sizeHeight = null;
         }

        root.sizeWidth = currentWidth;
        root.sizeHeight = currentHeight;

        const w = Math.round(root.sizeWidth);
        const h = Math.round(root.sizeHeight);

        if (w !== previousValues.width) {
            updateText$1(outputWidth, w);
            previousValues.width = w;
        }
        
        if (h !== previousValues.height) {
            updateText$1(outputHeight, h);
            previousValues.height = h;
        }
        
        if (currentPercentage !== previousValues.percentage) {
            if (image.width) {
                updateText$1(resizePercentage, `${ currentPercentage }%`);
            }
            else {
                updateText$1(resizePercentage, '');
            }
            previousValues.percentage = currentPercentage;
        }

    }
});
//#endif

const wrapper = (name, mixins) => createView({
    ignoreRect: true,
    name,
    mixins,
    create: ({ root, props }) => {

        if (props.className) {
            root.element.classList.add(props.className);
        }
        
        props.controls.map(control => {
            const view = root.createChildView(control.view, control);
            if (control.didCreateView) {
                control.didCreateView(view);
            }
            root.appendChildView(view);
        });
        
    }
});

const warn = () => console.log('Doka: localStorage not available');

const getData = id => {
    try {
        JSON.parse(localStorage.getItem(id) || '{}');
    }
    catch(e) {
        warn();
    }
    return {};
};

const setStoredValue = (id, key, value) => {
    const data = getData(id);
    data[key] = value;
    try {
        localStorage.setItem(id, JSON.stringify(data));
    }
    catch(e) {
        warn();
    }
    return value;
};

const getStoredValue = (id, key, defaultValue) => {
    const data = getData(id);
    return typeof data[key] === 'undefined' ? defaultValue : data[key];
};

const canHover = () => window.matchMedia('(pointer: fine) and (hover: hover)').matches;

//#if !_EXCLUDE_CROP_UTIL

const instructionsBubble = createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    name: 'instructions-bubble',
    mixins: {
        styles: [
            'opacity',
            'translateX',
            'translateY'
        ],
        animations: {
            'opacity': { type: 'tween', duration: 400 }
        },
        apis: [
            'seen'
        ]
    },
    create: ({ root, props }) => root.element.innerHTML = (props.iconBefore || '') + props.text,
    write: ({ root, props }) => {
        if (!props.seen) return;
        root.opacity = 0;
    }
});

const SPRING_TRANSLATE = {
    type: 'spring',
    stiffness: .4,
    damping: .65,
    mass: 7
};

const cropSubject = createView({
    name: 'crop-subject',
    ignoreRect: true,
    mixins: {
        styles: [
            'opacity',
            'translateX',
            'translateY'
        ],
        animations: {
            'opacity': { type: 'tween', duration: 250 },
            'translateX': SPRING_TRANSLATE,
            'translateY': SPRING_TRANSLATE
        }
    },
    create: ({ root, props }) => {

        // start at full opacity, it's only set to 0 when resizing
        root.opacity = 1;

        root.ref.timestampOffset = null;

        if (root.query('GET_CROP_ALLOW_INSTRUCTION_ZOOM') && canHover()) {
            const hasShown = getStoredValue(root.query('GET_STORAGE_NAME'), 'instruction_zoom_shown', false);
            if (!hasShown) {
                root.ref.instructions = root.appendChildView(
                    root.createChildView(
                        instructionsBubble,
                        {
                            opacity: 0,
                            seen: false,
                            text: root.query('GET_LABEL_CROP_INSTRUCTION_ZOOM'),
                            iconBefore: createIcon('<rect stroke-width="1.5" fill="none" stroke="currentColor" x="5" y="1" width="14" height="22" rx="7" ry="7"></rect><circle fill="currentColor" stroke="none" cx="12" cy="8" r="2"></circle>')
                        }
                    )
                );
            }
        }

        // create crop mask layer
        root.ref.maskView = root.appendChildView(
            root.createChildView(cropMask)
        );

        // create crop rectangle
        if (root.query('GET_CROP_ALLOW_RESIZE_RECT')) {
            root.ref.cropView = root.appendChildView(
                root.createChildView(cropRect)
            );
        }

        if (root.query('GET_CROP_SHOW_SIZE')) {
            root.ref.cropSize = root.appendChildView(
                root.createChildView(
                    cropSize, {
                        id: props.id,
                        opacity: 1,
                        scaleX: 1,
                        scaleY: 1,
                        translateX: null
                    }
                )
            );
        }

        if (!root.query('GET_CROP_ZOOM_TIMEOUT')) {
            root.ref.btnZoom = root.appendChildView(root.createChildView(
                wrapper('zoom-wrapper', {
                    styles: [
                        'opacity',
                        'translateX',
                        'translateY'
                    ],
                    animations: {
                        'opacity': { type: 'tween', duration: 250 }
                    },
                }), {
                opacity: 0,
                controls: [
                    {
                        view: button,
                        label: root.query('GET_LABEL_BUTTON_CROP_ZOOM'),
                        name: 'zoom',
                        icon: createIcon('<g fill="currentColor" fill-rule="nonzero"><path d="M12.5 19a6.5 6.5 0 1 1 0-13 6.5 6.5 0 0 1 0 13zm0-2a4.5 4.5 0 1 0 0-9 4.5 4.5 0 0 0 0 9z"/><path d="M15.765 17.18a1 1 0 1 1 1.415-1.415l3.527 3.528a1 1 0 0 1-1.414 1.414l-3.528-3.527z"/></g>', 26),
                        action: () => root.dispatch('CROP_ZOOM')
                    }
                ]
            }));
        }
    },
    write: createRoute({
        CROP_IMAGE_RESIZE_MULTIPLY: ({ root }) => {
            const { instructions } = root.ref;
            if (!instructions || instructions.seen) return;
            instructions.seen = true;
            setStoredValue(root.query('GET_STORAGE_NAME'), 'instruction_zoom_shown', true);
        },
        CROP_RECT_DRAG_RELEASE: ({ root, props, timestamp }) => {
            // center zoom button
            const { btnZoom } = root.ref;
            if (!btnZoom) return;
            const view = root.query('GET_CROP', props.id, timestamp);
            const rect = view.cropRect;
            const cx = rect.x + rect.width * .5;
            const cy = rect.y + rect.height * .5;
            btnZoom.translateX = cx;
            btnZoom.translateY = cy;
        }
    }, ({ root, props, timestamp }) => {

        // go!
        const { cropView, maskView, btnZoom, cropSize: cropSize$$1, instructions } = root.ref;

        // when is not active, move items out of view
        const isCropViewActive = root.query('IS_ACTIVE_VIEW', 'crop');
        if (!isCropViewActive && cropView) {
            cropView.enabled = false;
            root.ref.timestampOffset = null;
            if (cropSize$$1) {
                cropSize$$1.opacity = 0;
            }
            return;
        }

        // remember timestamp so we know when view was shown and can time instructions
        else if (!root.ref.timestampOffset) {
            root.ref.timestampOffset = timestamp;
        }


        /**
         * Update view
         */
        const view = root.query('GET_CROP', props.id, timestamp);
        if (!view) return;
        const { cropRect: cropRect$$1, isRotating, isDraft, scale } = view;

        // position subject
        const editorStageRect = root.query('GET_STAGE');
        root.translateX = editorStageRect.x - root.rect.element.left;
        root.translateY = editorStageRect.y - root.rect.element.top;
        
        // draw crop rectangle
        if (cropView) {
            cropView.draft = isDraft;
            cropView.rotating = isRotating;
            cropView.rectangle = cropRect$$1;
            cropView.enabled = true;
        }

        // draw size indicator
        if (cropSize$$1) {
            
            cropSize$$1.opacity = 1;

            if (isDraft) {
                cropSize$$1.translateX = null;
                cropSize$$1.translateY = null;
            }

            const offset = getCropSizeOffset(root.rect.element, cropSize$$1.rect.element, cropRect$$1);
            cropSize$$1.translateX = isDraft ? offset.x : autoPrecision(offset.x);
            cropSize$$1.translateY = isDraft ? offset.y : autoPrecision(offset.y);
        }

        // draw mask if enabled
        if (root.query('GET_CROP_MASK')) {

            if (isDraft) {
                maskView.translateX = null;
                maskView.translateY = null;
                maskView.maskWidth = null;
                maskView.maskHeight = null;
            }

            maskView.translateX = autoPrecision(cropRect$$1.x);
            maskView.translateY = autoPrecision(cropRect$$1.y);
            maskView.maskWidth = cropRect$$1.width;
            maskView.maskHeight = cropRect$$1.height;
            maskView.scale = scale;

        }

        // can recenter view
        if (view.canRecenter) {

            // hide instructions
            if (instructions) {
                instructions.opacity = 0;
            }

            // if has zoom button, show it
            if (btnZoom) {
                btnZoom.opacity = !view.isDraft ? 1 : 0;
            }
        }
        else {

            // hide zoom button
            if (btnZoom) {
                btnZoom.opacity = 0;
            }

            // if has instructions and is centered 
            if (instructions && !instructions.seen && !view.isDraft) {
                const cx = cropRect$$1.x + cropRect$$1.width * .5;
                const cy = cropRect$$1.y + cropRect$$1.height * .5;
                instructions.translateX = Math.round(cx - instructions.rect.element.width * .5);
                instructions.translateY = Math.round(cy - instructions.rect.element.height * .5);
                const activeTime = timestamp - root.ref.timestampOffset;
                if (activeTime > 2000) instructions.opacity = 1;
            }

        }

    })
    
});

const getCropSizeOffset = (stageRect, cropSizeRect, cropRect$$1) => {

    const margin = 16;
    const cropLeftEdge = cropRect$$1.x;
    const cropRightEdge = cropRect$$1.x + cropRect$$1.width;
    const cropBottomEdge = cropRect$$1.y + cropRect$$1.height;

    let x = cropRightEdge - cropSizeRect.width - margin;
    let y = cropBottomEdge - cropSizeRect.height - margin;

    // center below rectangle
    const isTooNarrowForStatus = cropSizeRect.width > cropRect$$1.width - (margin * 2);
    if (isTooNarrowForStatus) {
        
        // center horizontally
        x = cropLeftEdge + ((cropRect$$1.width * .5) - (cropSizeRect.width * .5));
        
        // position below box
        y = cropBottomEdge + margin;

        if (y > stageRect.height - cropSizeRect.height) {
            y = cropBottomEdge - cropSizeRect.height - margin;
        }
    }

    x = Math.max(0, Math.min(x, stageRect.width - cropSizeRect.width));
    
    return {
        x,
        y
    }
};
//#endif

const now = () => performance.now();

const throttle = (fn, limit) => {

    let timer = null;
    let last = null;

    return function() {

        if (!last) {
            fn.apply(null, Array.from(arguments));
            last = now();
            return;
        }
        
        clearTimeout(timer);
        timer = setTimeout(
            () => {
                if ((now() - last) >= limit) {
                    fn.apply(null, Array.from(arguments));
                    last = now();
                }
            },
            limit - (now() - last)
        );
    }
};

const climb = (element, fn) => {
    while (element.nodeType === 1 && !fn(element)) {
        element = element.parentNode;
    }
    return element.nodeType === 1 ? element : null;
};

//#if !_EXCLUDE_CROP_UTIL
const isMyTarget = (container, target) => {
    const parent = climb(target, el => el.classList.contains('doka--root'));
    return parent ? parent.contains(container) : false;
};

const updateIndicators = ({ root, props, action }) => {

    const { position } = action;
    const { pivotPoint } = props;
    const { indicatorA, indicatorB } = root.ref;

    const dx = pivotPoint.x - position.x;
    const dy = pivotPoint.y - position.y;

    const a = {
        x: pivotPoint.x + dx,
        y: pivotPoint.y + dy
    };

    const b = {
        x: pivotPoint.x - dx,
        y: pivotPoint.y - dy
    };

    indicatorA.style.cssText = `transform: translate3d(${a.x}px, ${a.y}px, 0)`;
    indicatorB.style.cssText = `transform: translate3d(${b.x}px, ${b.y}px, 0)`;
};

const getPositionFromEvent = (e) => ({
    x: e.pageX,
    y: e.pageY
});

const cropResize = createView({
    ignoreRect: true,
    ignoreRectUpdate: true,
    name: 'crop-resizer',
    mixins: {
        apis: ['pivotPoint', 'scrollRect']
    },
    create: ({ root, props }) => {

        root.ref.isActive = false;

        root.ref.isCropping = false;
        
        root.ref.indicatorA = document.createElement('div');
        root.appendChild(root.ref.indicatorA);
        
        root.ref.indicatorB = document.createElement('div');
        root.appendChild(root.ref.indicatorB);

        const MODIFIER_KEYS = root.query('GET_CROP_RESIZE_KEY_CODES');
        root.ref.hasEnabledResizeModifier = MODIFIER_KEYS.length > 0;

        const state = {
            origin: {
                x: null,
                y: null
            },
            position: {
                x: null,
                y: null
            },
            selecting: false,
            enabled: false,
            scrollY: 0,
            offsetX: 0,
            offsetY: 0
        };

        let throttleTimeStamp = now();

        root.ref.state = state;




        /**
         * Actual multi touch zooming for touch devices
         */
        const pointers = createPointerRegistry();
        let baseDistance = 0;
        let isMultiTouching = false;
        root.ref.resizeStart = e => {

            if (!root.ref.isActive) return;
            
            if (pointers.count() === 0) isMultiTouching = false;

            // should be targeted on this doka instance
            if (!isMyTarget(root.element, e.target)) return;

            pointers.push(e);
            addEvent$1(document.documentElement, 'up', root.ref.resizeEnd);

            // needs multiple pointers for resize action, collect one more
            if (!pointers.multiple()) return;

            e.stopPropagation();
            e.preventDefault();

            const active = pointers.active();
            const a = getPositionFromEvent(active[0]);
            const b = getPositionFromEvent(active[1]);
            baseDistance = vectorDistance(a, b);

            addEvent$1(document.documentElement, 'move', root.ref.resizeMove);

            isMultiTouching = true;
        };

        root.ref.resizeMove = (e) => {

            if (!root.ref.isActive) return;

            if (!isMultiTouching) return;
            
            e.preventDefault();

            if (pointers.count() !== 2) return;

            pointers.update(e);
            const active = pointers.active();
            const a = getPositionFromEvent(active[0]);
            const b = getPositionFromEvent(active[1]);
            
            const currentDistance = vectorDistance(a, b);
            const scalar = (currentDistance - baseDistance) / baseDistance;
            root.dispatch('CROP_IMAGE_RESIZE', { value: scalar });
        };

        root.ref.resizeEnd = (e) => {

            if (!root.ref.isActive) return;

            pointers.pop(e);

            const interactionFinished = pointers.count() === 0;
            if (interactionFinished) {
                removeEvent$1(document.documentElement, 'move', root.ref.resizeMove);
                removeEvent$1(document.documentElement, 'up', root.ref.resizeEnd);
            }

            if (!isMultiTouching) return;

            e.preventDefault();

            if (interactionFinished) {
                root.dispatch('CROP_IMAGE_RESIZE_RELEASE');
            }
        };
        
        addEvent$1(document.documentElement, 'down', root.ref.resizeStart);



        /**
         * Mousewheel zooming
         */
        let last = performance.now();
        let previousDirection = 0;
        let value = 1;
        const step = 100;
        const handleWheel = throttle(e => {

            if (root.ref.isCropping) return;

            const currentDirection = Math.sign(e.wheelDelta || e.deltaY);

            const stamp = now();
            const dist = stamp - last;
            last = stamp;

            if (dist > 750 || previousDirection !== currentDirection) {
                value = 1;
                previousDirection = currentDirection;
            }
            
            value += .05 * currentDirection;

            root.dispatch('CROP_IMAGE_RESIZE_MULTIPLY', { value: Math.max(0.1, value) });

            root.dispatch('CROP_IMAGE_RESIZE_RELEASE');

        }, step);

        root.ref.wheel = e => {

            if (!root.ref.isActive) return;

            if (!isMyTarget(root.element, e.target)) return;

            if (props.scrollRect) {
                const scrollRect = props.scrollRect;

                // check if is positioned in crop rectangle
                const rootRect = root.query('GET_ROOT');

                // get position
                const pageWheelPosition = getPositionFromEvent(e);
                const clientWheelPosition = {
                    x: pageWheelPosition.x - rootRect.leftScroll,
                    y: pageWheelPosition.y - rootRect.topScroll
                };

                if (
                    (clientWheelPosition.x < scrollRect.x) || 
                    (clientWheelPosition.x > scrollRect.x + scrollRect.width) ||
                    (clientWheelPosition.y < scrollRect.y) || 
                    (clientWheelPosition.y > scrollRect.y + scrollRect.height)
                ) {
                    // outside of crop rect
                    return;
                }
            }
            
            e.preventDefault();
            handleWheel(e);
        };

        document.addEventListener('wheel', root.ref.wheel, { passive: false });


        
        /**
         * Custom mouse multitouch solution
         */

        if (!root.ref.hasEnabledResizeModifier) return;

        root.ref.move = e => {

            if (!root.ref.isActive || root.ref.isCropping) return;

            state.position.x = e.pageX - root.ref.state.offsetX;
            state.position.y = e.pageY - root.ref.state.scrollY - root.ref.state.offsetY;

            if (!state.enabled) return;
            
            if (!isMyTarget(root.element, e.target)) {
                
                root.dispatch('RESIZER_CANCEL');

                return;
            }
            else if (root.element.dataset.state === 'idle') {

                root.dispatch('RESIZER_SHOW', {
                    position: {
                        ...state.position
                    }
                });

            }

            e.preventDefault();

            root.dispatch('RESIZER_MOVE', { position: { ...state.position } });

            const { pivotPoint } = props;

            // calculate resize
            const dx = pivotPoint.x - state.position.x;
            const dy = pivotPoint.y - state.position.y;

            const a = {
                x: pivotPoint.x + dx,
                y: pivotPoint.y + dy
            };
            const b = {...state.position};

            if (!state.selecting) {
                return;
            }

            const currentDistance = vectorDistance(a, b);
            const scalar = (currentDistance - baseDistance) / baseDistance;

            const now$$1 = performance.now();
            if (now$$1 - throttleTimeStamp > 25) {
                throttleTimeStamp = now$$1;
                root.dispatch('CROP_IMAGE_RESIZE', { value: scalar });
            }
        };

        root.ref.select = e => {

            if (!root.ref.isActive) return;

            if (!isMyTarget(root.element, e.target)) return;

            const { pivotPoint } = props;

            // calculate base distance
            const dx = pivotPoint.x - state.position.x;
            const dy = pivotPoint.y - state.position.y;

            const a = {
                x: pivotPoint.x + dx,
                y: pivotPoint.y + dy
            };
            const b = state.position;
            
            baseDistance = vectorDistance(a, b);

            state.selecting = true;
            state.origin.x = e.pageX;
            state.origin.y = e.pageY;
            root.dispatch('CROP_IMAGE_RESIZE_GRAB');
        };

        root.ref.confirm = e => {

            if (!root.ref.isActive) return;

            if (!isMyTarget(root.element, e.target)) return;

            state.selecting = false;
            root.dispatch('CROP_IMAGE_RESIZE_RELEASE');
        };

        root.ref.blur = () => {

            if (!root.ref.isActive) return;

            state.selecting = false;
            state.enabled = false;
            document.removeEventListener('mousedown', root.ref.select);
            document.removeEventListener('mouseup', root.ref.confirm);
            root.dispatch('RESIZER_CANCEL');
        };

        window.addEventListener('blur', root.ref.blur);

        // always listen to move event so spheres are in position when alt key is pressed
        document.addEventListener('mousemove', root.ref.move);
        
        root.ref.keyDown = e => {

            if (!root.ref.isActive) return;
            
            if (!MODIFIER_KEYS.includes(e.keyCode) || !state.position) return;

            state.enabled = true;

            document.addEventListener('mousedown', root.ref.select);
            document.addEventListener('mouseup', root.ref.confirm);

            root.dispatch('RESIZER_SHOW', {
                position: {
                    ...state.position
                }
            });
        };

        root.ref.keyUp = e => {

            if (!root.ref.isActive) return;

            if (!MODIFIER_KEYS.includes(e.keyCode)) return;

            state.enabled = false;

            document.removeEventListener('mousedown', root.ref.select);
            document.removeEventListener('mouseup', root.ref.confirm);

            root.dispatch('RESIZER_CANCEL');
        };
    
        // handle modifier key
        document.body.addEventListener('keydown', root.ref.keyDown);
        document.body.addEventListener('keyup', root.ref.keyUp);

    },
    destroy:({ root }) => {
        document.removeEventListener('touchmove', root.ref.resizeMove);
        document.removeEventListener('touchend', root.ref.resizeEnd);
        document.removeEventListener('touchstart', root.ref.resizeStart);
        document.removeEventListener('wheel', root.ref.wheel);
        document.removeEventListener('mousedown', root.ref.select);
        document.removeEventListener('mouseup', root.ref.confirm);

        if (!root.ref.hasEnabledResizeModifier) return;
        document.removeEventListener('mousemove', root.ref.move);
        document.body.removeEventListener('keydown', root.ref.keyDown);
        document.body.removeEventListener('keyup', root.ref.keyUp);
        window.removeEventListener('blur', root.ref.blur);
    },
    read: ({ root }) => {
        root.ref.state.scrollY = window.scrollY;
        const rect = root.element.getBoundingClientRect();
        root.ref.state.offsetX = rect.x;
        root.ref.state.offsetY = rect.y;
    },
    write: createRoute({
        CROP_RECT_DRAG_GRAB: ({ root }) => {
            root.ref.isCropping = true;
        },
        CROP_RECT_DRAG_RELEASE: ({ root }) => {
            root.ref.isCropping = false;
        },
        SHOW_VIEW: ({ root, action }) => {
            root.ref.isActive = action.id === 'crop';
        },
        RESIZER_SHOW: ({ root, props, action }) => {
            root.element.dataset.state = 'multi-touch';
            updateIndicators({ root, props, action });
        },
        RESIZER_CANCEL: ({ root }) => {
            root.element.dataset.state = 'idle';
        },
        RESIZER_MOVE: updateIndicators
    })
});
//#endif

//#if !_EXCLUDE_CROP_UTIL

const setOpacity = (el, v) => el.style.opacity = v;

const updateImageBoundsIcon = (root, shouldLimit) => {

    const parts = Array.from(root.element.querySelectorAll('.doka--icon-crop-limit rect'));
    
    if (!parts.length) return;

    setOpacity(parts[0], shouldLimit ? .3 : 0);
    setOpacity(parts[1], shouldLimit ? 1 : 0);

    setOpacity(parts[2], shouldLimit ? 0 : .3);
    setOpacity(parts[3], shouldLimit ? 0 : 1);
};

const updateAspectRatioIcon = (root, aspectRatio) => {

    const parts = root.element.querySelectorAll('.doka--icon-aspect-ratio rect');
    
    if (!parts.length) return;

    if (!aspectRatio) {
        setOpacity(parts[0], .2);
        setOpacity(parts[1], .3);
        setOpacity(parts[2], .4);
        return;
    }

    setOpacity(parts[0], aspectRatio > 1 ? 1 : .3);
    setOpacity(parts[1], aspectRatio === 1 ? .85 : .5);
    setOpacity(parts[2], aspectRatio < 1 ? 1 : .3);
};

const updateTurnIcons = (root, aspectRatio) => {

    // select svg element
    const rectangles = Array.from(root.element.querySelectorAll('.doka--icon-turn rect'));
    rectangles.forEach(rect => {

        // portrait
        if (aspectRatio > 1) {
            // if arrow precedes rectangle, move slightly less
            rect.setAttribute('x', rect.previousElementSibling ? 5 : 4);
            rect.setAttribute('width', 9);
        }

        // landscape
        if (aspectRatio < 1) {
            rect.setAttribute('y', 11);
            rect.setAttribute('height', 10);
        }
    
    });
};

const createRectangle = (aspectRatio) => {
    let width, height;
    if (aspectRatio > 1) {
        height = 14;
        width = Math.round(height / aspectRatio);
    }
    else {
        width = 14;
        height = Math.round(width * aspectRatio);
    }
    const x = Math.round((23 - width) * .5);
    const y = Math.round((23 - height) * .5);
    return `<svg width="23" height="23" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><g fill="currentColor"><rect x="${x}" y="${y}" width="${width}" height="${height}" rx="2.5"/></g></svg>`;
};

const cropRoot = createView({
    name: 'crop',
    ignoreRect: true,
    mixins: {
        apis: ['viewId', 'stagePosition', 'hidden', 'offsetTop']
    },
    create: ({ root, props }) => {

        props.viewId = 'crop';
        props.hidden = false;
        root.ref.isHiding = false;
        const controls = [];
        
        if (root.query('GET_CROP_ALLOW_IMAGE_TURN_LEFT')) {
            controls.push({
                view: button,
                name: 'tool',
                label: root.query('GET_LABEL_BUTTON_CROP_ROTATE_LEFT'),
                icon: createIcon('<g transform="translate(3 2)" fill="currentColor" fill-rule="evenodd" class="doka--icon-turn"><rect y="9" width="12" height="12" rx="1"/><path d="M9.823 5H11a5 5 0 0 1 5 5 1 1 0 0 0 2 0 7 7 0 0 0-7-7H9.626l.747-.747A1 1 0 0 0 8.958.84L6.603 3.194a1 1 0 0 0 0 1.415l2.355 2.355a1 1 0 0 0 1.415-1.414L9.823 5z" fill-rule="nonzero" /></g>', 26),
                action: () => root.dispatch('CROP_IMAGE_ROTATE_LEFT')
            });
        }

        if (root.query('GET_CROP_ALLOW_IMAGE_TURN_RIGHT')) {
            controls.push({
                view: button,
                name: 'tool',
                label: root.query('GET_LABEL_BUTTON_CROP_ROTATE_RIGHT'),
                icon: createIcon('<g transform="translate(5 2)" fill="currentColor" fill-rule="evenodd" class="doka--icon-turn"><path d="M8.177 5H7a5 5 0 0 0-5 5 1 1 0 0 1-2 0 7 7 0 0 1 7-7h1.374l-.747-.747A1 1 0 0 1 9.042.84l2.355 2.355a1 1 0 0 1 0 1.415L9.042 6.964A1 1 0 0 1 7.627 5.55l.55-.55z" fill-rule="nonzero"/><rect x="6" y="9" width="12" height="12" rx="1"/></g>', 26),
                action: () => root.dispatch('CROP_IMAGE_ROTATE_RIGHT')
            });
        }

        if (root.query('GET_CROP_ALLOW_IMAGE_FLIP_HORIZONTAL')) {
            controls.push({
                view: button,
                name: 'tool',
                label: root.query('GET_LABEL_BUTTON_CROP_FLIP_HORIZONTAL'),
                icon: createIcon('<g fill="currentColor" fill-rule="evenodd"><path d="M11.93 7.007V20a1 1 0 0 1-1 1H5.78a1 1 0 0 1-.93-1.368l5.15-12.993a1 1 0 0 1 1.929.368z"/><path d="M14 7.007V20a1 1 0 0 0 1 1h5.149a1 1 0 0 0 .93-1.368l-5.15-12.993A1 1 0 0 0 14 7.007z" opacity=".6"/></g>', 26),
                action: () => root.dispatch('CROP_IMAGE_FLIP_HORIZONTAL')
            });
        }
        if (root.query('GET_CROP_ALLOW_IMAGE_FLIP_VERTICAL')) {
            controls.push({
                view: button,
                name: 'tool',
                label: root.query('GET_LABEL_BUTTON_CROP_FLIP_VERTICAL'),
                icon: createIcon('<g fill="currentColor" fill-rule="evenodd"><path d="M19.993 12.143H7a1 1 0 0 1-1-1V5.994a1 1 0 0 1 1.368-.93l12.993 5.15a1 1 0 0 1-.368 1.93z"/><path d="M19.993 14a1 1 0 0 1 .368 1.93L7.368 21.078A1 1 0 0 1 6 20.148V15a1 1 0 0 1 1-1h12.993z" opacity=".6"/></g>', 26),
                action: () => root.dispatch('CROP_IMAGE_FLIP_VERTICAL')
            });
        }

        const aspectRatioOptions = root.query('GET_CROP_ASPECT_RATIO_OPTIONS');
        if (aspectRatioOptions && aspectRatioOptions.length) {
            controls.push({
                view: dropdown,
                name: 'tool',
                label: root.query('GET_LABEL_BUTTON_CROP_ASPECT_RATIO'),
                icon: createIcon('<g class="doka--icon-aspect-ratio" fill="currentColor" fill-rule="evenodd"><rect x="2" y="4" opacity=".3" width="10" height="18" rx="1"/><rect opacity=".5" x="4" y="8" width="14" height="14" rx="1"/><rect x="6" y="12" width="17" height="10" rx="1"/></g>', 26),
                options: null,
                onSelect: value => {
                    if (value.width && value.height) {
                        root.dispatch('RESIZE_SET_OUTPUT_SIZE', { width: value.width, height: value.height });
                    }
                    else {
                        const aspectRatioOptions = root.query('GET_CROP_ASPECT_RATIO_OPTIONS');
                        if (aspectRatioOptions.find(option => option.value && option.value.width || option.value.height)) {
                            root.dispatch('RESIZE_SET_OUTPUT_SIZE', { width: null, height: null });
                        }
                        root.dispatch('CROP_SET_ASPECT_RATIO', { value: value.aspectRatio });
                    }
                },
                didCreateView: view => {
                    root.ref.aspectRatioDropdown = view;
                }
            });
        }
        
        if (root.query('GET_CROP_ALLOW_TOGGLE_LIMIT')) {
            controls.push({
                view: dropdown,
                name: 'tool',
                label: root.query('GET_LABEL_BUTTON_CROP_TOGGLE_LIMIT'),
                icon: createIcon(`<g class="doka--icon-crop-limit" fill="currentColor" fill-rule="evenodd">
                    <rect x="2" y="3" width="20" height="20" rx="1"/>
                    <rect x="7" y="8" width="10" height="10" rx="1"/>
                    <rect x="4" y="8" width="14" height="14" rx="1"/>
                    <rect x="12" y="4" width="10" height="10" rx="1"/>
                </g>`, 26),
                options: [
                    {
                        value: true,
                        label: root.query('GET_LABEL_BUTTON_CROP_TOGGLE_LIMIT_ENABLE'),
                        icon: `<svg width="23" height="23" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><g fill="currentColor"><rect x="3" y="3" width="17" height="17" rx="2.5" opacity=".3"/><rect x="7" y="7" width="9" height="9" rx="2.5"/></g></svg>`
                    },
                    {
                        value: false,
                        label: root.query('GET_LABEL_BUTTON_CROP_TOGGLE_LIMIT_DISABLE'),
                        icon: `<svg width="23" height="23" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false"><g fill="currentColor"><rect x="3" y="6" width="13" height="13" rx="2.5" opacity=".3"/><rect x="10" y="3" width="9" height="9" rx="2.5"/></g></svg>`
                    }
                ],
                onSelect: value => {
                    root.dispatch('CROP_SET_LIMIT', { value });
                },
                didCreateView: view => {
                    root.ref.cropToggleLimitDropdown = view;
                }
            });

        }

        // add submenu container
        root.ref.menu = root.appendChildView(
            root.createChildView(
                createGroup('toolbar', ['opacity'], {
                    'opacity': {
                        type: 'spring',
                        mass: 15,
                        delay: 50
                    }
                }), {
                opacity: 0,
                controls
            })
        );

        root.ref.menuItemsRequiredWidth = null;

        root.ref.subject = root.appendChildView(
            root.createChildView(cropSubject, { ...props })
        );

        if (root.query('GET_CROP_ALLOW_ROTATE')) {
            root.ref.rotator = root.appendChildView(
                root.createChildView(cropRotator, {
                    rotation: 0,
                    opacity: 0,
                    translateY: 20,
                    id: props.id
                })
            );
        }

        root.ref.resizer = root.appendChildView(
            root.createChildView(cropResize, {
                pivotPoint: {
                    x: 0, 
                    y: 0
                }
            })
        );


        root.ref.updateControls = () => {
            const image = root.query('GET_IMAGE');
            updateTurnIcons(root, image.height / image.width);


            if (root.ref.cropToggleLimitDropdown) {
                root.ref.isLimitedToImageBounds = root.query('GET_CROP_LIMIT_TO_IMAGE_BOUNDS');
                updateImageBoundsIcon(root, root.ref.isLimitedToImageBounds);
                root.ref.cropToggleLimitDropdown.selectedValue = root.ref.isLimitedToImageBounds;
            }

            if (root.ref.aspectRatioDropdown) {
                const minImageSize = root.query('GET_MIN_IMAGE_SIZE');
                const filteredOptions = aspectRatioOptions.filter(option => {
                    
                    if (!option.value.aspectRatio) return true;

                    if (option.value.aspectRatio < 1) {
                        const w = image.naturalWidth;
                        const h = w * option.value.aspectRatio;
                        if (h < minImageSize.height) {
                            return false;
                        }
                    }
                    else {
                        const h = image.naturalHeight;
                        const w = h / option.value.aspectRatio;
                        if (w < minImageSize.width) {
                            return false;
                        }
                    }
                    return true;
                });

                root.ref.aspectRatioDropdown.options = filteredOptions.map(option => ({
                    ...option,
                    icon: createRectangle(option.value.aspectRatio)
                }));
            }


        };

        root.ref.isModal = /modal|fullscreen/.test(root.query('GET_STYLE_LAYOUT_MODE'));
    },

    read: ({ root, props }) => {

        if (props.hidden) {
            root.ref.menuItemsRequiredWidth = null;
            return;
        }

        const rootRect = root.rect;
        if (rootRect.element.width === 0 || rootRect.element.height === 0) return;
        
        if (root.ref.menuItemsRequiredWidth === null) {
            const width = root.ref.menu.childViews.reduce((prev, view) => prev + view.rect.outer.width, 0);
            root.ref.menuItemsRequiredWidth = width === 0 ? null : width;
        }

        // calculate position of stage
        const { left, top, width, height } = root.ref.subject.rect.element;
        props.stagePosition = {
            x: left,
            y: top,
            width: width,
            height: height
        };

    },

    shouldUpdateChildViews: ({ props, actions }) => {
        // should update if not hidden, or if hidden and has actions
        return !props.hidden || (props.hidden && actions && actions.length);
    },
    
    // recenter pivot point for resize view
    write: createRoute(
        {
            SHOW_VIEW: ({ root, action, props }) => {

                const { menu, rotator, subject }  = root.ref;
                
                // if is me, show
                if (props.viewId === action.id) {
                    subject.opacity = 1;
                    menu.opacity = 1;
                    if (rotator) {
                        rotator.opacity = 1;
                        rotator.translateY = 0;
                    }
                    props.hidden = false;
                    root.ref.isHiding = false;

                    root.ref.updateControls();
                }
                // if not is me, hide
                else {
                    subject.opacity = 0;
                    menu.opacity = 0;
                    if (rotator) {
                        rotator.opacity = 0;
                        rotator.translateY = 20;
                    }
                    root.ref.isHiding = true;
                }

            },
            UNLOAD_IMAGE: ({ root }) => {
                const { menu, rotator }  = root.ref;
                menu.opacity = 0;
                if (rotator) {
                    rotator.opacity = 0;
                    rotator.translateY = 20;
                }
            },
            DID_PRESENT_IMAGE: ({ root }) => {

                const { menu, rotator } = root.ref;
                menu.opacity = 1;
                if (rotator) {
                    rotator.opacity = 1;
                    rotator.translateY = 0;
                }

                root.ref.updateControls();
            }
        },
        ({ root, props, timestamp }) => {
        
            const { resizer, subject, menu, rotator, isHiding, cropToggleLimitDropdown, aspectRatioDropdown } = root.ref;
            const { hidden } = props;

            const childViewsAreInvisible = subject.opacity === 0 && menu.opacity === 0 && (!rotator || (rotator && rotator.opacity === 0));
            if (!hidden && isHiding && childViewsAreInvisible) {
                root.ref.isHiding = false;
                props.hidden = true;
            }

            if (props.hidden) return;

            const view = root.query('GET_CROP', props.id, timestamp);
            if (!view) return;

            if (aspectRatioDropdown) {

                const aspectRatio = root.query('GET_ACTIVE_CROP_ASPECT_RATIO');
                const size = root.query('GET_SIZE');
                const selectedValue = aspectRatioDropdown.selectedValue;

                if (!selectedValue) {
                    aspectRatioDropdown.selectedValue = {
                        aspectRatio,
                        width: size.width,
                        height: size.height
                    };
                    updateAspectRatioIcon(root, aspectRatio);
                }
                else {

                    if (selectedValue.aspectRatio !== aspectRatio) {
                        updateAspectRatioIcon(root, aspectRatio);
                    }

                    if (selectedValue.aspectRatio !== aspectRatio || selectedValue.width !== size.width || selectedValue.height !== size.height) {
                        aspectRatioDropdown.selectedValue = {
                            aspectRatio,
                            width: size.width,
                            height: size.height
                        };
                    }
                }

            }

            if (cropToggleLimitDropdown) {
                if (root.ref.isLimitedToImageBounds !== view.isLimitedToImageBounds) {
                    root.ref.isLimitedToImageBounds = view.isLimitedToImageBounds;
                    updateImageBoundsIcon(root, view.isLimitedToImageBounds);
                    cropToggleLimitDropdown.selectedValue = view.isLimitedToImageBounds;
                }
            }

            resizer.pivotPoint = {
                x: resizer.rect.element.width * .5,
                y: resizer.rect.element.height * .5
            };

            if (rotator) {
                rotator.animate = !view.isDraft;
                rotator.rotation = view.rotation.sub;
                rotator.setAllowInteraction(root.query('IS_ACTIVE_VIEW', 'crop'));    
            }
            
            menu.element.dataset.layout = root.ref.menuItemsRequiredWidth > root.ref.menu.rect.element.width ? 'compact' : 'spacious';

            if (root.query('GET_CROP_RESIZE_SCROLL_RECT_ONLY')) {
                const editorStageRect = root.query('GET_STAGE');
                const stageOffsetX = editorStageRect.x;
                const stageOffsetY = editorStageRect.y;

                const editorRoot = root.query('GET_ROOT');
    
                const editorOffsetX = root.ref.isModal ? editorRoot.left : 0;
                const editorOffsetY = root.ref.isModal ? editorRoot.top : 0;

                resizer.scrollRect = {
                    x: editorOffsetX + stageOffsetX + view.cropRect.x,
                    y: editorOffsetY + stageOffsetY + view.cropRect.y + props.offsetTop,
                    width: view.cropRect.width,
                    height: view.cropRect.height
                };
            }
        }
    )
});

//#endif

//#if !_EXCLUDE_RESIZE_UTIL
const sizeInput = createView({
    name: 'size-input',
    mixins: {
        listeners: true,
        apis: [
            'id',
            'value',
            'placeholder',
            'getValue',
            'setValue',
            'setPlaceholder',
            'hasFocus',
            'onChange'
        ]
    },
    create: ({ root, props }) => {

        const { id, min, max, value, placeholder, onChange = () => {}, onBlur = () => {} } = props;

        // setup field and label
        const uid = `doka--${id}-${getUniqueId()}`;
        const input = createElement('input', {
            type: 'number', 
            step: 1,
            id: uid,
            min,
            max,
            value,
            placeholder
        });

        // get max characters from field
        const maxLength = input.getAttribute('max').length;

        // set label
        const label = createElement('label', { 'for': uid });
        label.textContent = props.label;

        // formats the value before it's saved to the field
        const formatValue = (value, min, max) => {

            if (isString(value)) {

                // remove special characters
                value = value.replace(/[^0-9]/g, '');

                // limit length
                if (value.length > maxLength) {
                    value = value.slice(0, maxLength);
                }

                // to number (size can't be a fraction)
                value = parseInt(value, 10);
            }
            else {
                // make sure it's not a fraction
                value = Math.round(value);
            }

            return isNaN(value) ? null : limit(value, min, max);
        };

        const asNumber = value => value.length ? parseInt(input.value, 10) : null;

        // format the input value based on min and max
        root.ref.handleInput = () => {
            input.value = formatValue(input.value, 1, max);
            onChange(asNumber(input.value));
        };

        root.ref.handleBlur = () => {
            input.value = formatValue(input.value, min, max);
            onBlur(asNumber(input.value));
        };

        input.addEventListener('input', root.ref.handleInput);
        input.addEventListener('blur', root.ref.handleBlur);

        root.appendChild(input);
        root.appendChild(label);

        // reference input
        root.ref.input = input;

        // public methods
        props.hasFocus = () => input === document.activeElement;
        props.getValue = () => asNumber(input.value);
        props.setValue = (value) => input.value = value ? formatValue(value, 1, 999999) : null;
        props.setPlaceholder = (value) => input.placeholder = value;
    },
    destroy: ({ root }) => {
        root.ref.input.removeEventListener('input', root.ref.handleInput);
        root.ref.input.removeEventListener('blur', root.ref.handleBlur);
    }
});
//#endif

//#if !_EXCLUDE_RESIZE_UTIL
const checkboxInput = createView({
    name: 'checkable',
    tag: 'span',
    mixins: {
        listeners: true,
        apis: [
            'id',
            'checked',
            'onChange',
            'onSetValue',
            'setValue',
            'getValue'
        ]
    },
    create: ({ root, props }) => {

        const { id, checked, onChange = () => {}, onSetValue = () => {} } = props;

        const uid = `doka--${id}-${getUniqueId()}`;
        const input = createElement('input', { type: 'checkbox', value: 1, id:uid });
        input.checked = checked;
        root.ref.input = input;

        const label = createElement('label', { 'for': uid });
        label.innerHTML = props.label;

        root.appendChild(input);
        root.appendChild(label);

        // format the input value based on min and max
        root.ref.handleChange = () => {
            onSetValue(input.checked);
            onChange(input.checked);
        };
        input.addEventListener('change', root.ref.handleChange);
        
        props.getValue = () => input.checked;
        props.setValue = value => {
            input.checked = value;
            onSetValue(input.checked);
        };

        setTimeout(() => {onSetValue(input.checked);}, 0);
    },
    destroy: ({ root }) => {
        root.ref.input.removeEventListener('change', root.ref.handleChange);
    }
});
//#endif

let testResult$2 = null;
const isAndroid = () => {
    if (testResult$2 === null) {
        testResult$2 = /Android/i.test(navigator.userAgent);
    }
    return testResult$2;
};

//#if !_EXCLUDE_RESIZE_UTIL
const resizeForm = createView({
    ignoreRect: true,
    name: 'resize-form',
    tag: 'form',
    mixins: {
        styles: [ 'opacity' ],
        animations: { 'opacity': { type: 'spring', mass: 15, delay: 150 } }
    },
    create: ({ root }) => {

        // don't trigger validation on this form
        root.element.setAttribute('novalidate', 'novalidate');

        // this makes sure the blue go button is shown on the iOS keyboard
        root.element.setAttribute('action', '#');

        // need to know if this is iOS so we can do some trickery when the keyboard is shown
        root.ref.shouldBlurKeyboard = isIOS() || isAndroid();
        
        // size limits
        const maxSize = root.query('GET_SIZE_MAX');
        const minSize = root.query('GET_SIZE_MIN');
        
        // will make sure values in fields are correct
        const balanceFields = ({ axisLock = 'none', enforceLimits = false } = {}) => {

            const { inputImageWidth, inputImageHeight, buttonConfirm } = root.ref;
            const shouldRespectAspectRatio = root.query('GET_SIZE_ASPECT_RATIO_LOCK');
            const cropRectangleAspectRatio = root.query('GET_CROP_RECTANGLE_ASPECT_RATIO');
            
            const draftSize = {
                width: inputImageWidth.getValue(),
                height: inputImageHeight.getValue()
            };

            const limitedSize = limitSize(
                draftSize, 
                enforceLimits ? minSize : { width: 1, height: 1 }, 
                enforceLimits ? maxSize : { width: 999999, height: 999999 }, 
                shouldRespectAspectRatio ? cropRectangleAspectRatio : null,
                axisLock
            );

            if (shouldRespectAspectRatio) {
                if (axisLock === 'width') {
                    inputImageHeight.setValue(limitedSize.width / cropRectangleAspectRatio);
                }
                else if (axisLock === 'height') {
                    inputImageWidth.setValue(limitedSize.height * cropRectangleAspectRatio);
                }
                else {
                    inputImageWidth.setValue(limitedSize.width || limitedSize.height * cropRectangleAspectRatio);
                    inputImageHeight.setValue(limitedSize.height || limitedSize.width / cropRectangleAspectRatio);
                }
            }
            else {
                if (limitedSize.width && !limitedSize.height) {
                    const autoHeight = Math.round(limitedSize.width / cropRectangleAspectRatio);
                    const limitedAutoSize = limitSize(
                        { width: limitedSize.width, height: autoHeight }, 
                        enforceLimits ? minSize : { width: 1, height: 1 }, 
                        enforceLimits ? maxSize : { width: 999999, height: 999999 }, 
                        cropRectangleAspectRatio,
                        axisLock
                    );
                    if (enforceLimits) {
                        inputImageWidth.setValue(Math.round(limitedAutoSize.width));
                    }
                    inputImageHeight.setPlaceholder(Math.round(limitedAutoSize.height));
                }
                else if (limitedSize.height && !limitedSize.width) {
                    const autoWidth = Math.round(limitedSize.height * cropRectangleAspectRatio);
                    inputImageWidth.setPlaceholder(autoWidth);
                }
            }

            // test if size has changed since previous confirmed size
            const { width, height } = root.query('GET_SIZE_INPUT');
            const previousWidth = isNumber(width) ? Math.round(width) : null;
            const previousHeight = isNumber(height) ? Math.round(height) : null;
            const newWidth = inputImageWidth.getValue();
            const newHeight = inputImageHeight.getValue();
            const hasImageSizeChanged = newWidth !== previousWidth || newHeight !== previousHeight;
            buttonConfirm.opacity = hasImageSizeChanged ? 1 : 0;

            // kick render engine so button fades in
            root.dispatch('KICK');

            return {
                width: inputImageWidth.getValue(),
                height: inputImageHeight.getValue()
            }
        };

        // reference needed to be able to assign root references in the field group
        const formRoot = root;
        root.appendChildView(
            root.createChildView(
                createFieldGroup('Image size', ({ root }) => {

                    const size = root.query('GET_SIZE');

                    const inputImageWidth = root.appendChildView(
                        root.createChildView(sizeInput, {
                            id: 'image-width',
                            label: root.query('GET_LABEL_RESIZE_WIDTH'),
                            value: isNumber(size.width) ? Math.round(size.width) : null,
                            min: minSize.width,
                            max: maxSize.width,
                            placeholder: 0,
                            onChange: () => balanceFields({ axisLock: 'width' }),
                            onBlur: () => balanceFields({ enforceLimits: false })
                        })
                    );

                    const aspectRatioLock = root.appendChildView(
                        root.createChildView(checkboxInput, {
                            id: 'aspect-ratio-lock',
                            label: createIcon('<g fill="none" fill-rule="evenodd"><path stroke="currentColor" stroke-width="1.5" stroke-linecap="round" class="doka--aspect-ratio-lock-ring" d="M9.401 10.205v-.804a2.599 2.599 0 0 1 5.198 0V14"/><rect fill="currentColor" x="7" y="10" width="10" height="7" rx="1.5"/></g>'),
                            checked: root.query('GET_SIZE_ASPECT_RATIO_LOCK'),
                            onSetValue: checked => {
                                const ring = aspectRatioLock.element.querySelector('.doka--aspect-ratio-lock-ring');
                                const offset = checked ? 0 : -3;
                                ring.setAttribute('transform', `translate(0 ${offset})`);
                            },
                            onChange: value => {
                                root.dispatch('RESIZE_SET_OUTPUT_SIZE_ASPECT_RATIO_LOCK', { value });
                                balanceFields();
                            }
                        })
                    );

                    const inputImageHeight = root.appendChildView(
                        root.createChildView(sizeInput, {
                            id: 'image-height',
                            label: root.query('GET_LABEL_RESIZE_HEIGHT'),
                            value: isNumber(size.height) ? Math.round(size.height) : null,
                            min: minSize.height,
                            max: maxSize.height,
                            placeholder: 0,
                            onChange: () => balanceFields({ axisLock: 'height' }),
                            onBlur: () => balanceFields({ enforceLimits: false })
                        })
                    );

                    formRoot.ref.aspectRatioLock = aspectRatioLock;
                    formRoot.ref.inputImageWidth = inputImageWidth;
                    formRoot.ref.inputImageHeight = inputImageHeight;
                })
            )
        );

        root.ref.buttonConfirm = root.appendChildView(root.createChildView(button, {
            name: 'app action-confirm icon-only',
            label:  root.query('GET_LABEL_RESIZE_APPLY_CHANGES'),
            action: () => {},
            opacity: 0,
            icon: createIcon('<polyline fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="20 6 9 17 4 12"></polyline>'),
            type: 'submit'
        }));

        // handle form submit
        root.ref.confirmForm = e => {

            // no axis lock as we have to make sure the limits are respected
            const outputSize = balanceFields({ enforceLimits: true });

            // we never want to actually submit the form
            e.preventDefault();

            const { shouldBlurKeyboard, buttonConfirm } = root.ref;

            // only do this if on iOS
            if (shouldBlurKeyboard) {
                document.activeElement.blur(); // hide the keyboard
                buttonConfirm.element.focus(); // move focus to somewhere
            }
            buttonConfirm.opacity = 0;

            // no need to limit the size here as the RESIZE_SET_OUTPUT_SIZE action will limit the values
            root.dispatch('RESIZE_SET_OUTPUT_SIZE', outputSize);
        };
        
        root.element.addEventListener('submit', root.ref.confirmForm);
    },
    destroy: ({ root }) => {
        root.element.removeEventListener('submit', root.ref.confirmForm);
    },
    write: createRoute(
        {
            EDIT_RESET: ({ root }) => {
                const size = root.query('GET_SIZE');
                const { inputImageWidth, inputImageHeight, aspectRatioLock, buttonConfirm } = root.ref;
                inputImageWidth.setValue(size.width);
                inputImageHeight.setValue(size.height);
                aspectRatioLock.setValue(root.query('GET_SIZE_ASPECT_RATIO_LOCK'));
                buttonConfirm.opacity = 0;
            },
            RESIZE_SET_OUTPUT_SIZE: ({ root, action }) => {
                const { inputImageWidth, inputImageHeight } = root.ref;
                inputImageWidth.setValue(action.width);
                inputImageHeight.setValue(action.height);
            },
            CROP_SET_ASPECT_RATIO: ({ root, props, action, timestamp }) => {
                const view = root.query('GET_CROP', props.id, timestamp);
                if (!view) return;
                const { cropStatus } = view;
                const { inputImageWidth, inputImageHeight } = root.ref;

                // set to free aspect ratio
                if (action.value === null) {
                    if (inputImageWidth.getValue() && inputImageHeight.getValue()) {
                        inputImageHeight.setValue(null);
                        inputImageHeight.setPlaceholder(cropStatus.crop.height);
                    }
                    return;
                }
                
                inputImageWidth.setValue(cropStatus.image.width);
                inputImageWidth.setPlaceholder(cropStatus.crop.width);

                inputImageHeight.setValue(cropStatus.image.height);
                inputImageHeight.setPlaceholder(cropStatus.crop.height);
            }
        },
        ({ root, props, timestamp }) => {

            const view = root.query('GET_CROP', props.id, timestamp);
            if (!view) return;

            // don't update if is invisible
            if (root.opacity <= 0);
            
            const { cropStatus } = view;
            const { inputImageWidth, inputImageHeight } = root.ref;

            if (inputImageWidth.hasFocus() || inputImageHeight.hasFocus()) return;

            const cropRectangleAspectRatio = root.query('GET_CROP_RECTANGLE_ASPECT_RATIO');
            
            if (inputImageWidth.getValue() === null && inputImageHeight.getValue() === null) {
                inputImageWidth.setPlaceholder(cropStatus.crop.width);
                inputImageHeight.setPlaceholder(cropStatus.crop.height);
            }

            else if (inputImageWidth.getValue() === null && cropStatus.image.height !== null) {
                const autoWidth = Math.round(cropStatus.image.height * cropRectangleAspectRatio);
                inputImageWidth.setPlaceholder(autoWidth);
            }

            else if (inputImageHeight.getValue() === null && cropStatus.image.width !== null) {
                const autoHeight = Math.round(cropStatus.image.width / cropRectangleAspectRatio);
                inputImageHeight.setPlaceholder(autoHeight);
            }
              
    })
});

const createFieldGroup = (label, create) => createView({
    tag: 'fieldset',
    create: ({ root }) => {
        const legend = createElement('legend');
        legend.textContent = label;
        root.element.appendChild(legend);
        create({ root });
    }
});
//#endif

//#if !_EXCLUDE_RESIZE_UTIL

const resizeRoot = createView({
    name: 'resize',
    ignoreRect: true,
    mixins: {
        apis: ['viewId', 'stagePosition', 'hidden']
    },
    create: ({ root, props }) => {

        props.viewId = 'resize';
        props.hidden = false;
        root.ref.isHiding = false;

        root.ref.form = root.appendChildView(
            root.createChildView(resizeForm, { opacity: 0, id: props.id })
        );

    },
    read: ({ root, props }) => {

        if (props.hidden) return;
        const rootRect = root.rect;
        if (rootRect.element.width === 0 || rootRect.element.height === 0) return;
        
        // calculate position of stage
        const formRect = root.ref.form.rect;

        props.stagePosition = {
            x: rootRect.element.left,
            y: rootRect.element.top + formRect.element.height,
            width: rootRect.element.width,
            height: rootRect.element.height - formRect.element.height
        };

    },

    shouldUpdateChildViews: ({ props, actions }) => {
        // should update if not hidden, or if hidden and has actions
        return !props.hidden || (props.hidden && actions && actions.length);
    },
    
    write: createRoute({
        SHOW_VIEW: ({ root, action, props }) => {
            
            // if is me, show
            if (action.id === props.viewId) {
                root.ref.isHiding = false;
                root.ref.form.opacity = 1;
            }
            // if not is me, hide
            else {
                root.ref.isHiding = true;
                root.ref.form.opacity = 0;
            }

        }
    }, ({ root, props }) => {

        const { form, isHiding } = root.ref;
        const { hidden } = props;

        if (isHiding && form.opacity === 0 && !hidden) {
            props.hidden = true;
        }
        else {
            props.hidden = false;
        }

    })
});

//#endif

//#if !_EXCLUDE_COLOR_UTIL
const rangeInput = createView({
    name: 'range-input',
    tag: 'span',
    mixins: {
        listeners: true,
        apis: [
            'onUpdate',
            'setValue',
            'getValue',
            'setAllowInteraction'
        ]
    },
    create: ({ root, props }) => {

        const { id, min, max, step, value, onUpdate = () => {} } = props;

        let uid = `doka--${id}-${getUniqueId()}`;

        const input = createElement('input', { type: 'range', id: uid, min, max, step });
        input.value = value;
        root.ref.input = input;

        const wrapper = createElement('span');
        wrapper.className = 'doka--range-input-inner';

        const label = createElement('label', { 'for': uid });
        label.innerHTML = props.label;

        const centerValue = min + (max - min) * .5;
        root.element.dataset.centered = value === centerValue;

        root.ref.handleRecenter = () => {
            props.setValue(centerValue);
            root.ref.handleChange();
        };

        const recenter = createElement('button', { type: 'button' });
        recenter.textContent = 'center';
        recenter.addEventListener('click', root.ref.handleRecenter);
        root.ref.recenter = recenter;

        wrapper.appendChild(input);
        wrapper.appendChild(recenter);
        root.appendChild(label);
        root.appendChild(wrapper);

        root.ref.handleChange = () => {
            const value = props.getValue();
            root.element.dataset.centered = value === centerValue;
            onUpdate(value);
        };
        input.addEventListener('input', root.ref.handleChange);
        
        // fix for mobile input
        let rect = null;
        root.ref.dragger = createDragger(
            wrapper,
            () => {rect = input.getBoundingClientRect();},
            e => {
                const position = (e.pageX - rect.left) / rect.width;
                input.value = min + (position * (max - min));
                root.ref.handleChange();
            },
            () => {},
            {
                stopPropagation: true
            }
        );

        props.getValue = () => parseFloat(input.value);
        props.setValue = value => input.value = value;
        props.setAllowInteraction = (enabled) => {
            if (enabled) {
                root.ref.dragger.enable();
            }
            else {
                root.ref.dragger.disable();
            }
        };
    },
    destroy: ({ root }) => {
        root.ref.dragger.destroy();
        root.ref.recenter.removeEventListener('click', root.ref.handleRecenter);
        root.ref.input.removeEventListener('input', root.ref.handleChange);
    }
});
//#endif

//#if !_EXCLUDE_COLOR_UTIL

const COLOR_TOOLS$1 = {
    'brightness': {
        icon: createIcon('<g fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="7"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></g>')
    }, 
    'contrast': {
        icon: createIcon('<g fill="none" fill-rule="evenodd"><circle stroke="currentColor" stroke-width="3" cx="12" cy="12" r="10"/><path d="M12 2v20C6.477 22 2 17.523 2 12S6.477 2 12 2z" fill="currentColor"/></g>')
    }, 
    'exposure': {
        icon: createIcon('<g fill="none" fill-rule="evenodd"><rect stroke="currentColor" stroke-width="3" x="2" y="2" width="20" height="20" rx="4"/><path d="M20.828 3.172L3.172 20.828A3.987 3.987 0 0 1 2 18V6a4 4 0 0 1 4-4h12c1.105 0 2.105.448 2.828 1.172zM7 7H5v2h2v2h2V9h2V7H9V5H7v2zM12 15h5v2h-5z" fill="currentColor"/></g>')
    }, 
    'saturation': {
        icon: createIcon('<g fill="none" fill-rule="evenodd"><rect stroke="currentColor" stroke-width="3" x="2" y="2" width="20" height="20" rx="4"/><path fill="currentColor" opacity=".3" d="M7 2.5h5v18.75H7z"/><path fill="currentColor" opacity=".6" d="M12 2.5h5v18.75h-5z"/><path fill="currentColor" opacity=".9" d="M17 2.5h4v18.75h-4z"/></g>')
    }
};

const colorForm = createView({
    ignoreRect: true,
    name: 'color-form',
    tag: 'form',
    mixins: {
        styles: [ 'opacity' ],
        animations: { 'opacity': { type: 'spring', mass: 15 } }
    },
    create: ({ root }) => {

        // don't trigger validation on this form
        root.element.setAttribute('novalidate', 'novalidate');

        const colorValues = root.query('GET_COLOR_VALUES');

        root.ref.tools = Object.keys(COLOR_TOOLS$1)
            .reduce((obj, tool) => {

                const id = tool;
                const icon = COLOR_TOOLS$1[tool].icon;
                const label = root.query(`GET_LABEL_COLOR_${tool.toUpperCase()}`);
                const range = root.query(`GET_COLOR_${tool.toUpperCase()}_RANGE`);
                const value = colorValues[tool];
                
                obj[id] = {
                    view: root.appendChildView(
                        root.createChildView(rangeInput, {
                            id,
                            label: `${icon}<span>${label}</span>`,
                            min: range[0],
                            max: range[1],
                            step: 0.01,
                            value,
                            onUpdate: value => root.dispatch('COLOR_SET_COLOR_VALUE', { key: id, value })
                        })
                    )
                };

                return obj;

            }, {});
    },
    write: createRoute({
        'COLOR_SET_VALUE': ({ root, action }) => {
            root.ref.tools[action.key].view.setValue(action.value);
        },
        'SHOW_VIEW': ({ root, action }) => {
            Object.keys(root.ref.tools).forEach(tool => {
                root.ref.tools[tool].view.setAllowInteraction(action.id === 'color');
            });
        }
    })
});

//#endif

//#if !_EXCLUDE_FILTER_UTIL
let tilePreviewWorker = null;
let tilePreviewWorkerTerminationTimeout = null;

const updateColors = (root, colors) => {
    const { brightness, exposure, contrast, saturation } = colors;
    if (brightness !== 0) {
        const isDarkened = brightness < 0;
        const b = isDarkened ? 'multiply' : 'overlay';
        const c = isDarkened ? 0 : 255;
        const o = isDarkened ? Math.abs(brightness) : 1 - brightness;
        root.ref.imageOverlay.style.cssText = `mix-blend-mode: ${b}; background: rgba(${c},${c},${c},${o})`;
    }
    root.ref.imageOverlay.style.cssText = 'background:transparent';
    root.ref.image.style.cssText = `filter: brightness(${exposure}) contrast(${contrast}) saturate(${saturation})`;
    return colors;
};

const colorKeys = Object.keys(COLOR_TOOLS$1);
const colorEquals = (a, b) => colorKeys.findIndex(v => a[v] !== b[v]) < 0;

const createFilterTile = (index) => createView({
    ignoreRect: true,
    tag: 'li',
    name: 'filter-tile',
    mixins: {
        styles: ['opacity', 'translateY'],
        animations: {
            'translateY': { type: 'spring', delay: index * 10 },
            'opacity': { type: 'spring', delay: index * 30 }
        }
    },
    create: ({ root, props }) => {

        const id = `doka--filter-${ props.style }-${getUniqueId()}`;
        
        const button = createElement('input', { id, 'type': 'radio', 'name': 'filter' });
        root.appendChild(button);
        button.checked = props.selected;
        button.value = props.style;
        button.addEventListener('change', () => {
            if (button.checked) {
                props.onSelect();
            }
        });

        const label = createElement('label', { 'for': id });
        label.textContent = props.label;
        root.appendChild(label);

        const imageData = props.imageData;

        const width = Math.min(imageData.width, imageData.height);
        const height = width;

        const image = createElement('canvas');
        image.width = width;
        image.height = height;
        const ctx = image.getContext('2d');
        root.ref.image = image;

        const imageOverlay = createElement('div');
        root.ref.imageOverlay = imageOverlay;
        const offset = {
            x: (width * .5) - (imageData.width * .5),
            y: (height * .5) - (imageData.height * .5)
        };

        const imageWrapper = createElement('div');
        imageWrapper.appendChild(image);
        imageWrapper.appendChild(imageOverlay);
        root.appendChild(imageWrapper);
        root.ref.imageWrapper = imageWrapper;

        // use normal image and exit if no matrix defined
        if (!props.matrix) {
            ctx.putImageData(imageData, offset.x, offset.y);
            return;
        }

        // send to the preview worker
        if (!tilePreviewWorker) {
            tilePreviewWorker = createWorker(TransformWorker);
        }
        clearTimeout(tilePreviewWorkerTerminationTimeout);
        tilePreviewWorker.post(
            {
                transforms: [{
                    type: 'filter',
                    data: props.matrix
                }],
                imageData
            },
            response => {
                ctx.putImageData(response, offset.x, offset.y);
                clearTimeout(tilePreviewWorkerTerminationTimeout);
                tilePreviewWorkerTerminationTimeout = setTimeout(() => {
                    tilePreviewWorker.terminate();
                    tilePreviewWorker = null;
                }, 1000);
            },
            [imageData.data.buffer]
        );
        
        root.ref.activeColors = updateColors(root, root.query('GET_COLOR_VALUES'));
    },
    write:({ root }) => {
        if (root.opacity <= 0) return;
        const newColors = root.query('GET_COLOR_VALUES');
        const { activeColors } = root.ref;
        if ((!activeColors && newColors) || (!colorEquals(activeColors, newColors))) {
            root.ref.activeColors = newColors;
            updateColors(root, newColors);
        }
    }
});
//#endif

const cloneImageData = imageData => {
    let id;
    try {
        id = new ImageData(imageData.width, imageData.height);
    }
    catch(e) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        id = ctx.createImageData(imageData.width, imageData.height);
    }
    id.data.set(new Uint8ClampedArray(imageData.data));
    return id;
};

const arrayEqual = (a, b) => 
    Array.isArray(a) && Array.isArray(b) && 
    a.length === b.length && 
    a.every((value, index) => value === b[index]);

//#if !_EXCLUDE_FILTER_UTIL
const filterList = createView({
    ignoreRect: true,
    tag: 'ul',
    name: 'filter-list',
    mixins: {
        apis: [
            'filterOpacity',
            'hidden'
        ]
    },
    create: ({ root, props }) => {

        root.element.setAttribute('role', 'list');
        root.ref.tiles = [];

        const thumbData = root.query('GET_THUMB_IMAGE_DATA');
        
        const filtersDefinition = root.query('GET_FILTERS');
        const filters = [];
        
        // to array
        forin(filtersDefinition, (key, filter) => {
            filters.push({
                id: key,
                ...filter
            });
        });

        // active filter
        root.ref.activeFilter = root.query('GET_FILTER');

        // generate the thumbnail tiles
        root.ref.tiles = filters.map((filter, index) => {

            const matrix = filter.matrix();

            const isSelected = root.ref.activeFilter === filter.id || arrayEqual(root.ref.activeFilter, matrix) || index === 0;

            return root.appendChildView(
                root.createChildView(createFilterTile(index), {
                    opacity: 0,
                    translateY: -5,
                    id: props.id,
                    style: filter.id,
                    label: filter.label,
                    matrix,
                    imageData: cloneImageData(thumbData),
                    selected: isSelected,
                    onSelect: () => root.dispatch('FILTER_SET_FILTER', {
                        value: matrix ? filter.id : null
                    })
                })
            );
        });
    },

    write: ({ root, props }) => {

        if (props.hidden) { return; }

        const filter = root.query('GET_FILTER');
        if (filter !== root.ref.activeFilter) {

            root.ref.activeFilter = filter;
            
            const filters = root.query('GET_FILTERS');
            const matrixName = !filter ? 'original' :
                isString(filter) ? 
                    // use name (name should be in list)
                    filter : 
                    // find name of matrix in filters list (so it can be selected)
                    isColorMatrix(filter) ? 
                        Object.keys(filters).find(key => arrayEqual(filters[key].matrix(), filter)) :
                        // fallback to null (no selected item)
                        null;
    
            // check boxes
            Array.from(root.element.querySelectorAll('input'))
                .forEach(input => input.checked = input.value === matrixName);    
        }
        
        if (root.query('IS_ACTIVE_VIEW', 'filter')) {
            root.ref.tiles.forEach(tile => {
                tile.opacity = 1;
                tile.translateY = 0;
            });
        }
        else {
            root.ref.tiles.forEach(tile => {
                tile.opacity = 0;
                tile.translateY = -5;
            });
        }

        props.filterOpacity = root.ref.tiles.reduce((prev, tile) => prev + tile.opacity, 0) / root.ref.tiles.length;
    }
});
//#endif

//#if !_EXCLUDE_FILTER_UTIL
const filterScroller = createView({
    name: 'filter-scroller',
    ignoreRect: true,
    ignoreRectUpdate: true,
    mixins: {
        styles: ['opacity'],
        animations: {
            'opacity': { type: 'spring' }
        },
        apis: [
            'hidden',
            'filterOpacity'
        ]
    },
    create: ({ root, props }) => {

        root.ref.filters = root.appendChildView(
            root.createChildView(filterList, { id: props.id })
        );

        // this is a scroll container
        root.element.isScrollContainer = true;

        // add some nice additional interactions to mouse and touchpad devices
        if (!canHover()) return;

        // prevent back and forward navigation gestures on MacOS
        root.ref.handleMouseWheel = e => {
            const scrollLeft = root.element.scrollLeft;
            const scrollRange = root.ref.scrollWidth - root.rect.element.width;
            const offset = scrollLeft + e.deltaX;
            if (offset < 0 || offset > scrollRange) {
                root.element.scrollLeft = Math.min(Math.max(offset, 0), scrollRange);
                e.preventDefault();
            }  
        };
        root.element.addEventListener('mousewheel', root.ref.handleMouseWheel);
    
        // allow drag scroll with mouse / touchpad
        {
            root.element.dataset.dragState = 'end';
            let startOffsetLeft;
            root.ref.dragger = createDragger(
                root.ref.filters.element,
                () => {
                    root.element.dataset.dragState = 'start';
                    startOffsetLeft = root.element.scrollLeft;
                },
                (e, offset) => {
                    root.element.scrollLeft = startOffsetLeft - offset.x;
                    if (vectorDistanceSquared({ x: 0, y: 0 }, offset) > 0) {
                        root.element.dataset.dragState = 'dragging';
                    }
                },
                () => {
                    root.element.dataset.dragState = 'end';
                },
                {
                    stopPropagation: true
                }
            );
        }
    },
    destroy: ({ root }) => {
        if (root.ref.handleMouseWheel) {
            root.element.removeEventListener('mousewheel', root.ref.handleMouseWheel);
        }
        if (root.ref.dragger) {
            root.ref.dragger.destroy();
        }
    },
    read: ({ root }) => {
        root.ref.scrollWidth = root.element.scrollWidth;
    },
    write: ({ root, props }) => {
        root.ref.filters.hidden = props.hidden;
        props.filterOpacity = root.ref.filters.filterOpacity;
    }
});

const filterRoot = createView({
    name: 'filter',
    ignoreRect: true,
    mixins: {
        apis: ['viewId', 'stagePosition', 'hidden']
    },
    create: ({ root, props }) => {

        props.viewId = 'filter';
        props.hidden = false;
        root.ref.isHiding = false;

        root.ref.filters = root.appendChildView(
            root.createChildView(filterScroller, { id: props.id })
        );

    },
    read: ({ root, props }) => {
        
        if (!root.ref.filters || props.hidden) return;

        const rootRect = root.rect;
        if (rootRect.element.width === 0 || rootRect.element.height === 0) return;

        const filterRect = root.ref.filters.rect;

        const areFiltersAlignedTop = filterRect.element.top === 0;
        
        const y = areFiltersAlignedTop ? 
            rootRect.element.top + filterRect.element.height + filterRect.element.marginBottom : 
            rootRect.element.top;
        
        const height = areFiltersAlignedTop ? 
            rootRect.element.height - filterRect.element.height - filterRect.element.marginBottom :
            rootRect.element.height - filterRect.element.height - rootRect.element.top;
        
        props.stagePosition = {
            x: rootRect.element.left,
            y,
            width: rootRect.element.width,
            height
        };

    },
    shouldUpdateChildViews: ({ props, actions }) => {
        // should update if not hidden, or if hidden and has actions
        return !props.hidden || (props.hidden && actions && actions.length);
    },
    write: createRoute({
        SHOW_VIEW: ({ root, action, props }) => {
            
            if (!root.ref.filters) return;
            
            // if is me, show
            if (action.id === props.viewId) {
                root.ref.isHiding = false;
                props.hidden = false;
                root.ref.filters.hidden = false;
            }
            // if not is me, hide
            else {
                root.ref.isHiding = true;
            }

        }
    }, ({ root, props }) => {

        root.ref.filters.opacity = root.ref.isHiding || root.ref.filters.hidden ? 0 : 1;
        if (root.ref.isHiding && root.ref.filters.filterOpacity <= 0) {
            root.ref.isHiding = false;
            props.hidden = true;
            root.ref.filters.hidden = true;
        }
    })

});
//#endif

//#if !_EXCLUDE_COLOR_UTIL
const colorRoot = createView({
    name: 'color',
    ignoreRect: true,
    mixins: {
        apis: ['viewId', 'stagePosition', 'hidden']
    },
    create: ({ root, props }) => {

        props.viewId = 'color';
        props.hidden = false;
        root.ref.isHiding = false;

        root.ref.form = root.appendChildView(
            root.createChildView(colorForm, { opacity: 0, id: props.id })
        );
        
    },
    
    read: ({ root, props }) => {

        if (props.hidden) return;
        const rootRect = root.rect;
        if (rootRect.element.width === 0 || rootRect.element.height === 0) return;
        
        const formRect = root.ref.form.rect;
        const formHeight = formRect.element.height;
        const isFormAlignedTop = formRect.element.top === 0;

        const y = isFormAlignedTop ? 
            rootRect.element.top + formHeight : 
            rootRect.element.top;
        
        const height = isFormAlignedTop ? 
            rootRect.element.height - formHeight :
            rootRect.element.height - formHeight - rootRect.element.top;
        
        props.stagePosition = {
            x: rootRect.element.left,
            y,
            width: rootRect.element.width,
            height
        };
        

    },
    shouldUpdateChildViews: ({ props, actions }) => {
        // should update if not hidden, or if hidden and has actions
        return !props.hidden || (props.hidden && actions && actions.length);
    },
    write: createRoute({
        SHOW_VIEW: ({ root, action, props }) => {
            
            // if is me, show
            if (action.id === props.viewId) {
                root.ref.isHiding = false;
                root.ref.form.opacity = 1;
                props.hidden = false;
            }
            // if not is me, hide
            else {
                root.ref.isHiding = true;
                root.ref.form.opacity = 0;
            }
        }
    }, ({ root, props }) => {
        if (root.ref.isHiding && root.ref.form.opacity === 0) {
            root.ref.isHiding = false;
            props.hidden = true;
        }
    })
});
//#endif

//#if !_EXCLUDE_MARKUP_UTIL
const supportsColorPicker = () => {
    try {
        const input = createElement('input', { type: 'color' });
        const isColorType = input.type === 'color';
        if (/^((?!chrome|android).)*safari/i.test(navigator.userAgent)) {
            return isColorType;
        }
        return isColorType && typeof input.selectionStart !== 'number';
    }
    catch (e) {
        return false;
    }
};


const toHSL = (r, g, b) => {
    let max = Math.max(r, g, b), min = Math.min(r, g, b),
        d = max - min,
        h,
        s = (max === 0 ? 0 : d / max),
        v = max / 255;

    switch (max) {
        case min: h = 0; break;
        case r: h = (g - b) + d * (g < b ? 6: 0); h /= 6 * d; break;
        case g: h = (b - r) + d * 2; h /= 6 * d; break;
        case b: h = (r - g) + d * 4; h /= 6 * d; break;
    }
    return [h, s, v];
};

const markupColor = createView({
    ignoreRect: true,
    tag: 'div',
    name: 'markup-color',
    mixins: {
        animations: {
            'opacity': 'spring'
        },
        styles: [
            'opacity'
        ],
        apis: [
            'onSelect',
            'selectedValue'
        ]
    },
    create: ({ root, props }) => {

        const { colors, name, onSelect } = props;

        root.ref.handleChange = e => {
            onSelect(e.target.value);
            e.stopPropagation();
        };

        root.element.addEventListener('change', root.ref.handleChange);

        const list = createElement('ul');
        root.ref.inputs = colors.map(c => {
            const id = 'doka--color-' + getUniqueId();
            const option = createElement('li');
            const radio = createElement('input', {
                id,
                name,
                type: 'radio',
                value: c[1]
            });
            const label = createElement('label', {
                'for': id,
                title: c[0],
                style: 'background-color: ' + (c[2] || c[1])
            });
            label.textContent = c[0];
            appendChild(option)(radio);
            appendChild(option)(label);
            appendChild(list)(option);
            return radio;
        });
        root.element.appendChild(list);


        const shouldRenderColorPicker = root.query('GET_MARKUP_ALLOW_CUSTOM_COLOR') && supportsColorPicker();

        if (!shouldRenderColorPicker) return;

        const colorInput = createElement('div', {
            'class': 'doka--color-input'
        });

        const id = 'doka--color-' + getUniqueId();
        const colorInputLabel = createElement('label', {
            'for': id
        });
        colorInputLabel.textContent = 'Choose color';

        const colorInputField = createElement('input', {
            id,
            name,
            type: 'color'
        });

        const colorInputVisualizer = createElement('span', {'class': 'doka--color-visualizer'});
        const colorInputBrightness = createElement('span', {'class': 'doka--color-brightness'});

        root.ref.handleCustomColorChange = () => {
            const rgb = toRGBColorArray(colorInputField.value);
            const hsl = toHSL(...rgb);
            const rotation = (hsl[0] * 360) - 90;
            const offset = hsl[1] * .625;
            const brightness = 1 - hsl[2];
            colorInputVisualizer.style.backgroundColor = colorInputField.value;
            colorInputVisualizer.style.transform = `rotateZ(${rotation}deg) translateX(${offset}em)`;
            colorInputBrightness.style.opacity = brightness;
            onSelect(colorInputField.value);
        };

        let isFirstInteraction = true;
        root.ref.handleCustomColorSelect = e => {

            if (!isFirstInteraction) {
                root.ref.handleCustomColorChange();
            }
            else {
                onSelect(e.target.value);
            }
        

            isFirstInteraction = false;
        };

        colorInputField.addEventListener('click', root.ref.handleCustomColorSelect);
        colorInputField.addEventListener('input', root.ref.handleCustomColorChange);
        
        appendChild(colorInput)(colorInputField);
        appendChild(colorInput)(colorInputLabel);
        appendChild(colorInput)(colorInputVisualizer);
        appendChild(colorInput)(colorInputBrightness);
        root.appendChild(colorInput);

        root.ref.customInput = colorInputField;
    },
    write: ({ root, props }) => {
        if (props.selectedValue !== root.ref.activeSelectedValue) {
            root.ref.activeSelectedValue = props.selectedValue;

            // we use the found variable to determine if the current selected value is in the colors array
            let found = false;
            root.ref.inputs.forEach(input => {
                input.checked = input.value === props.selectedValue;
                if (input.checked) {
                    found = true;
                }
            });

            // exit if no custom color input defined
            if (!root.ref.customInput) return;

            // test if this was adefault color, and if colors are available, if note, preselect color picker
            root.ref.customInput.dataset.selected = root.ref.inputs.length && !found;

            if (!found) {
                root.ref.customInput.value = props.selectedValue;
                root.ref.handleCustomColorChange();
            }
            
        }
    },
    destroy: ({ root }) => {
        root.element.removeEventListener('change', root.ref.handleChange);

            // exit if no custom color input defined
        if (!root.ref.customInput) return;

        root.ref.customInput.removeEventListener('click', root.ref.handleCustomColorSelect);
        root.ref.customInput.removeEventListener('input', root.ref.handleCustomColorChange);
    }
});
//#endif

//#if !_EXCLUDE_MARKUP_UTIL

const showDrawTool = root => {
    const { 
        colorSelect, 
        fontFamilySelect, fontSizeSelect, 
        shapeStyleSelect, lineStyleSelect, 
        lineDecorationSelect
    } = root.ref;

    [
        fontFamilySelect,
        fontSizeSelect,
        shapeStyleSelect,
        lineDecorationSelect
    ].forEach(tool => {
        tool.element.dataset.active = 'false';
    });

    [
        colorSelect, 
        lineStyleSelect
    ].forEach(tool => {
        tool.element.dataset.active = 'true';
    });
};

const ALL_SETTINGS = [
    'fontFamily', 
    'fontSize', 
    'fontWeight',
    'textAlign',
    'backgroundColor',
    'fontColor',
    'borderColor',
    'borderWidth',
    'borderStyle',
    'lineColor',
    'lineWidth',
    'lineDecoration',
    'lineJoin',
    'lineCap'
];

const createSVG = (inner) => `<svg width="23" height="23" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">${inner}</svg>`;

const createShapeStyleIcon = (value) => {
    const fill = value === 0 ? 'currentColor' : 'none';
    const stroke = value === 0 ? 'none' : 'currentColor';
    const width = value;
    return createSVG(`<rect stroke="${stroke}" fill="${fill}" stroke-width="${width}" x="2" y="3" width="17" height="17" rx="3"/>`);
};

const createLineStyleIcon = (value) => {
    return createSVG(`<line stroke-linecap="round" stroke-linejoin="round" stroke="currentColor" stroke-width="${value}" x1="3" y1="12" x2="20" y2="12"/>`);
};

const markupTools = createView({
    name: 'markup-tools',
    ignoreRect: true,
    mixins: {
        apis: [
            'onUpdate'
        ],
        animations: {
            'translateY': 'spring',
            'opacity': 'spring'
        },
        styles: [
            'translateY',
            'opacity'
        ]
    },
    create: ({ root, props }) => {

        const { onUpdate } = props;

        // colors
        root.ref.colorSelect = root.appendChildView(root.createChildView(markupColor, {
            onSelect: value => {
                root.ref.colorSelect.selectedValue = value;
                onUpdate('color', value);
            },
            name: 'color-select',
            colors: root.query('GET_MARKUP_COLOR_OPTIONS')
        }));


        // shape styles
        root.ref.shapeStyleSelect = root.appendChildView(root.createChildView(dropdown, {
            onSelect: value => {
                root.ref.shapeStyleSelect.selectedValue = value;
                onUpdate('shapeStyle', [value[1], value[2]]);
            },
            name: 'tool',
            label: root.query('GET_LABEL_MARKUP_SELECT_SHAPE_STYLE'),
            direction: 'up',
            options: root.query('GET_MARKUP_SHAPE_STYLE_OPTIONS').map(item => ({
                 value: item,
                 label: item[0],
                 icon: createShapeStyleIcon(item[3])
            }))
        }));
        
        root.ref.lineStyleSelect = root.appendChildView(root.createChildView(dropdown, {
            onSelect: value => {
                root.ref.lineStyleSelect.selectedValue = value;
                onUpdate('lineStyle', [value[1], value[2]]);
            },
            name: 'tool',
            label: root.query('GET_LABEL_MARKUP_SELECT_LINE_STYLE'),
            direction: 'up',
            options: root.query('GET_MARKUP_LINE_STYLE_OPTIONS').map(item => ({
                 value: item,
                 label: item[0],
                 icon: createLineStyleIcon(item[3])
            }))
        }));


        // line decoration
        root.ref.lineDecorationSelect = root.appendChildView(root.createChildView(dropdown, {
            onSelect: value => {
                root.ref.lineDecorationSelect.selectedValue = value;
                onUpdate('lineDecoration', value);
            },
            name: 'tool',
            label: root.query('GET_LABEL_MARKUP_SELECT_LINE_DECORATION'),
            direction: 'up',
            options: root.query('GET_MARKUP_LINE_DECORATION_OPTIONS').map(item => ({
                 value: item[1],
                 label: item[0]
            }))
        }));


        // fonts
        root.ref.fontFamilySelect = root.appendChildView(root.createChildView(dropdown, {
            onSelect: value => {
                root.ref.fontFamilySelect.selectedValue = value;
                onUpdate('fontFamily', value);
            },
            name: 'tool',
            label: root.query('GET_LABEL_MARKUP_SELECT_FONT_FAMILY'),
            direction: 'up',
            options: root.query('GET_MARKUP_FONT_FAMILY_OPTIONS').map(item => ({
                 value: item[1],
                 label: `<span style="font-family:${item[1]};font-weight:600;">${item[0]}</span>`
            }))
        }));

        root.ref.fontSizeSelect = root.appendChildView(root.createChildView(dropdown, {
            onSelect: value => {
                root.ref.fontSizeSelect.selectedValue = value;
                onUpdate('fontSize', value);
            },
            name: 'tool',
            label: root.query('GET_LABEL_MARKUP_SELECT_FONT_SIZE'),
            direction: 'up',
            options: root.query('GET_MARKUP_FONT_SIZE_OPTIONS').map(item => ({
                 value: item[1],
                 label: item[0]
            }))
        }));

        if (root.query('GET_MARKUP_UTIL') === 'draw') {
            showDrawTool(root);
        }

    },
    write: createRoute({
        SWITCH_MARKUP_UTIL: ({ root, action }) => {
            const { util } = action;
            if (util !== 'draw') return;
            showDrawTool(root);
        },
        MARKUP_SELECT: ({ root, action }) => {

            const { 
                colorSelect, 
                fontFamilySelect, fontSizeSelect, 
                shapeStyleSelect, lineStyleSelect, 
                lineDecorationSelect
            } = root.ref;

            const activeMarkup = action.id ? root.query('GET_MARKUP_BY_ID', action.id) : null;
            
            const tools = [
                colorSelect,
                fontFamilySelect,
                fontSizeSelect,
                shapeStyleSelect,
                lineStyleSelect,
                lineDecorationSelect
            ];
            
            let activeTools = [];

            // no marker selected
            if (!activeMarkup) return;

            // get type
            const [type, settings] = activeMarkup;

            const foundSettings = Array.isArray(settings.allowEdit) ? 
                settings.allowEdit : 
                settings.allowEdit === false ? [] : ALL_SETTINGS;
            
            const allowEdit = ALL_SETTINGS.reduce((prev, curr) => {
                prev[curr] = foundSettings.indexOf(curr) !== -1;
                return prev;
            }, {});

            // generic color test
            allowEdit.color = !!foundSettings.find(setting => /[a-z]Color/.test(setting));
            
            if (type !== 'image') {
                if (allowEdit.color) {
                    colorSelect.selectedValue = getColor$2(settings);
                    activeTools.push(colorSelect);
                }
            }

            if (type === 'text') {
                if (allowEdit.fontFamily) {
                    fontFamilySelect.selectedValue = settings.fontFamily;
                    activeTools.push(fontFamilySelect);
                }
                if (allowEdit.fontSize) {
                    fontSizeSelect.selectedValue = settings.fontSize;
                    activeTools.push(fontSizeSelect);
                }
            }

            if (type === 'rect' || type === 'ellipse') {
                if (allowEdit.borderStyle) {

                    // all options
                    const options = root.query('GET_MARKUP_SHAPE_STYLE_OPTIONS');
                    const activeOption = options.find(option => {
                        
                        // matches line width
                        const widthMatch = settings.borderWidth === option[1];
                        
                        // matches line style
                        const styleMatch = 
                            (settings.borderStyle === option[2]) || 
                            arrayEqual(settings.borderStyle, option[2]);
                        
                        // hit?
                        return widthMatch && styleMatch;
                    });

                    shapeStyleSelect.selectedValue = activeOption;

                    activeTools.push(shapeStyleSelect);
                }
            }

            if (type === 'line' || type === 'path') {

                if (allowEdit.lineWidth) {

                    // all options
                    const options = root.query('GET_MARKUP_LINE_STYLE_OPTIONS');
                    const activeOption = options.find(option => {

                        // matches line width
                        const widthMatch = settings.lineWidth === option[1];
                        
                        // matches line style
                        const styleMatch = 
                            (settings.lineStyle === option[2]) || 
                            arrayEqual(settings.lineStyle, option[2]);
                        
                        // hit?
                        return widthMatch && styleMatch;
                    });
                    lineStyleSelect.selectedValue = activeOption;
                    activeTools.push(lineStyleSelect);
                }

                if (type === 'line' && allowEdit.lineDecoration) {
                    lineDecorationSelect.selectedValue = settings.lineDecoration;
                    activeTools.push(lineDecorationSelect);
                }
            }

            tools.forEach(tool => {
                tool.element.dataset.active = 'false';
            });

            activeTools.forEach(tool => {
                tool.element.dataset.active = 'true';
            });

        },
        MARKUP_UPDATE: ({ root, action }) => {
            const { style, value } = action;
            if (!root.ref[style + 'Select']) return;
            root.ref[style + 'Select'].selectedValue = value;
        }
    })
});

const getColor$2 = (settings) => {
    const { fontColor, backgroundColor, lineColor, borderColor } = settings;
    return fontColor || backgroundColor || lineColor || borderColor;
};
//#endif

//#if !_EXCLUDE_MARKUP_UTIL
const markupRoot = createView({
    name: 'markup',
    ignoreRect: true,
    mixins: {
        apis: ['viewId', 'stagePosition', 'hidden']
    },
    create: ({ root, props }) => {

        props.viewId = 'markup';
        props.hidden = false;
        root.ref.isHiding = false;

        const tools = [
            ['select', { label: root.query('GET_LABEL_MARKUP_TOOL_SELECT'), icon: createIcon('<g fill="none" fill-rule="evenodd"><path d="M7 13H5a1 1 0 01-1-1V5a1 1 0 011-1h7a1 1 0 011 1v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><path d="M10.22 8.914l12.58 5.18a1 1 0 01.012 1.844l-4.444 1.904a1 1 0 00-.526.526l-1.904 4.444a1 1 0 01-1.844-.013l-5.18-12.58a1 1 0 011.305-1.305z" fill="currentColor"/></g>', 26) }],
            ['draw', { label: root.query('GET_LABEL_MARKUP_TOOL_DRAW'), icon: createIcon('<g fill="currentColor"><path d="M17.86 5.71a2.425 2.425 0 013.43 3.43L9.715 20.714 5 22l1.286-4.715L17.86 5.71z"/></g>', 26) }],
            ['line', { label: root.query('GET_LABEL_MARKUP_TOOL_LINE'), icon: createIcon('<g transform="translate(3 4.5)" fill-rule="nonzero" fill="currentColor" stroke="none"><path d="M15.414 9.414l-6.01 6.01a2 2 0 1 1-2.829-2.828L9.172 10H2a2 2 0 1 1 0-4h7.172L6.575 3.404A2 2 0 1 1 9.404.575l6.01 6.01c.362.363.586.863.586 1.415s-.224 1.052-.586 1.414z"/></g>', 26) }],
            ['text', { label: root.query('GET_LABEL_MARKUP_TOOL_TEXT'), icon: createIcon('<g transform="translate(5 5)" fill="currentColor" fill-rule="evenodd"><path d="M10 4v11a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4H1a1 1 0 0 1-1-1V1a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-5z"/></g>', 26) }],
            ['rect', { label: root.query('GET_LABEL_MARKUP_TOOL_RECT'), icon: createIcon('<g fill="currentColor"><rect x="5" y="5" width="16" height="16" rx="2"/></g>', 26) }],
            ['ellipse', { label: root.query('GET_LABEL_MARKUP_TOOL_ELLIPSE'), icon: createIcon('<g fill="currentColor"><circle cx="13" cy="13" r="9"/></g>', 26) }]
        ];

        root.ref.utils = createElement('fieldset');
        root.ref.utils.className = 'doka--markup-utils';
        root.ref.utilsList = createElement('ul');
        const group = `markup-utils-${getUniqueId()}`;
        root.ref.inputs = tools.map(tool => {

            const key = tool[0];
            const props = tool[1];

            const id = 'doka--markup-tool-' + getUniqueId();
            const option = createElement('li');
            const radio = createElement('input');
            radio.id = id;
            radio.checked = root.query('GET_MARKUP_UTIL') === key;
            radio.setAttribute('type', 'radio');
            radio.setAttribute('name', group);
            radio.value = key;

            const label = createElement('label');
            label.setAttribute('for', id);
            label.className = 'doka--button-tool';
            label.innerHTML = props.icon + '<span>' + props.label + '</span>';
            label.title = props.label;

            option.appendChild(radio);
            option.appendChild(label);
            root.ref.utilsList.appendChild(option);
            return radio;
        });
        root.ref.utils.appendChild(root.ref.utilsList);
        root.ref.utilsList.addEventListener('change', e => {
            root.dispatch('SET_MARKUP_UTIL', { value: e.target.value });
        });

        if (root.query('GET_MARKUP_ALLOW_ADD_MARKUP')) {

            // add submenu container
            root.ref.menu = root.appendChildView(
                root.createChildView(
                    createGroup('toolbar', ['opacity'], {
                        'opacity': {
                            type: 'spring',
                            mass: 15,
                            delay: 50
                        }
                    }), {
                    opacity: 0,
                    element: root.ref.utils
                })
            );

        }
        
        const toolsRef = root.ref.tools = root.appendChildView(
            root.createChildView(
                markupTools,
                {
                    opacity: 0,
                    onUpdate: (style, value) => {
                        root.dispatch('MARKUP_UPDATE', { style, value });
                    }
                }
            )
        );

        root.ref.menuItemsRequiredWidth = null;

        if (root.query('GET_MARKUP_UTIL') === 'draw') {
            toolsRef.opacity = 1;
            toolsRef.translateY = 0;
            toolsRef.element.dataset.active = 'true';
        }
        
    },
    read: ({ root, props }) => {

        if (props.hidden) {
            root.ref.menuItemsRequiredWidth = null;
            return;
        }

        const rootRect = root.rect;
        if (rootRect.element.width === 0 || rootRect.element.height === 0) return;
        
        if (root.ref.menu && root.ref.menuItemsRequiredWidth === null) {
            const width = root.ref.menu.rect.element.width;
            root.ref.menuItemsRequiredWidth = width === 0 ? null : width;
        }

        const menuRect = root.ref.menu && root.ref.menu.rect;
        const toolRect = root.ref.tools.rect;
        const toolHeight = toolRect.element.height;
        const menuHeight = menuRect ? menuRect.element.height : toolHeight;
        const isMenuAlignedTop = menuRect ? menuRect.element.top === 0 : true;

        const y = isMenuAlignedTop ? 
            rootRect.element.top + menuHeight : 
            rootRect.element.top;
        
        const height = isMenuAlignedTop ? 
            rootRect.element.height - menuHeight :
            rootRect.element.height - menuHeight - rootRect.element.top;
        
        props.stagePosition = {
            x: rootRect.element.left + 20,
            y,
            width: rootRect.element.width - 40,
            height: height - toolHeight
        };

    },
    shouldUpdateChildViews: ({ props, actions }) => {
        // should update if not hidden, or if hidden and has actions
        return !props.hidden || (props.hidden && actions && actions.length);
    },
    write: createRoute({
        SHOW_VIEW: ({ root, action, props }) => {
            
            // if is me, show
            if (action.id === props.viewId) {
                props.hidden = false;
                root.ref.isHiding = false;
                if (root.ref.menu) {
                    root.ref.menu.opacity = 1;
                }
            }
            // if not is me, hide
            else {
                root.ref.isHiding = true;
                if (root.ref.menu) {
                    root.ref.menu.opacity = 0;
                }
                root.ref.tools.opacity = 0;
                root.ref.tools.translateY = 5;
            }

        },
        MARKUP_SELECT: ({ root, action }) => {
            root.ref.tools.opacity = action.id ? 1 : 0;
            root.ref.tools.translateY = action.id ? 0 : 5;
            root.ref.tools.element.dataset.active = action.id ? 'true' : 'false';
        },
        DID_SET_MARKUP_UTIL: ({ root, action }) => {

            const { inputs, tools } = root.ref;

            inputs.forEach(input => {
                input.checked = input.value === action.value;
            });

            if (action.value === 'draw') {
                tools.opacity = 1;
                tools.translateY = 0;
                tools.element.dataset.active = 'true';
            }

        }
    }, ({ root, props }) => {
        
        if (root.ref.isHiding && (root.ref.menu && root.ref.menu.opacity === 0)) {
            root.ref.isHiding = false;
            props.hidden = true;
        }

        if (props.hidden) return;

        root.ref.menu.element.dataset.layout = root.ref.menuItemsRequiredWidth > root.rect.element.width ? 'compact' : 'spacious';
    })
});
//#endif

const hasStagePositionChanged = (a, b) => {
    if (!a || !b) return true;
    return !rectEqualsRect(a, b);
};

const VIEW_MAP = {
    //#if !_EXCLUDE_CROP_UTIL
    'crop': cropRoot,
    //#endif
    //#if !_EXCLUDE_RESIZE_UTIL
    'resize': resizeRoot,
    //#endif
    //#if !_EXCLUDE_FILTER_UTIL
    'filter': filterRoot,
    //#endif
    //#if !_EXCLUDE_COLOR_UTIL
    'color': colorRoot,
    //#endif
    //#if !_EXCLUDE_MARKUP_UTIL
    'markup': markupRoot
    //#endif
};

const viewStack = createView({
    name: 'view-stack',
    ignoreRect: true,
    mixins: {
        apis: [
            'offsetTop'
        ]
    },
    create: ({ root }) => {
        root.ref.activeView = null;
        root.ref.activeStagePosition = null;
        root.ref.shouldFocus = false;
    },
    write: createRoute({
        SHOW_VIEW: ({ root, props, action }) => {

            const isFirstView = root.childViews.length === 0;

            // determine if view already available, if not, add
            let view = root.childViews.find(view => view.viewId === action.id);
            if (!view) {
                view = root.appendChildView(
                    root.createChildView(VIEW_MAP[action.id], { ...props })
                );
            }

            // make this view active
            root.ref.activeView = view;

            // disable child views
            root.childViews
                .map(view => view.element)
                .forEach(element => {
                    element.dataset.viewActive = 'false';
                    element.removeAttribute('tabindex');

                    // part of IE11 fix below
                    if (isIE()) {
                        element.style.transform = '';
                    }
                });

            // activate active view (moves view to front with CSS)
            const activeElement = root.ref.activeView.element;
            activeElement.dataset.viewActive = 'true';
            activeElement.setAttribute('tabindex', -1);

            // fixes problem where util wasn't redrawn on IE11
            if (isIE()) {
                setTimeout(() => {
                    activeElement.style.transform = 'translateZ(0)';
                }, 32);
            }

            root.ref.shouldFocus = !isFirstView;
        },
        DID_PRESENT_IMAGE: ({ root }) => {
            root.dispatch('CHANGE_VIEW', {
                id: root.query('GET_UTIL') || root.query('GET_UTILS')[0]
            });
        },
        DID_SET_UTILS: ({ root }) => {
            root.dispatch('CHANGE_VIEW', {
                id: root.query('GET_UTIL') || root.query('GET_UTILS')[0]
            });
        }
    }, ({ root, props }) => {

        const { activeView, previousStagePosition } = root.ref;
        if (!activeView || !activeView.stagePosition) return;

        root.childViews
            .forEach(view => {
                view.offsetTop = props.offsetTop;
                if (view.element.viewHidden !== view.hidden) {
                    view.element.viewHidden = view.hidden;
                    view.element.dataset.viewHidden = view.hidden;
                }
            });
            
        if (hasStagePositionChanged(activeView.stagePosition, previousStagePosition)) {

            // update root stage
            const { x, y, width, height } = activeView.stagePosition;

            if (width === 0 && height === 0) return;

            root.dispatch('DID_RESIZE_STAGE', {
                offset: {
                    x,
                    y
                },
                size: {
                    width,
                    height
                },
                animate: true
            });

            // remember
            root.ref.previousStagePosition = activeView.stagePosition;
        }

    }),
    didWriteView: ({ root }) => {
        if (root.ref.shouldFocus) {
            root.ref.activeView.element.focus({ preventScroll: true });
            root.ref.shouldFocus = false;
        }
    }
});

const editContent = createView({
    name: 'content',
    ignoreRect: true,
    mixins: {
        styles: [ 'opacity' ],
        animations: { 'opacity': { type: 'tween', duration: 250 } }
    },
    create: ({ root, props }) => {
        root.opacity = 1;
        root.ref.viewStack = root.appendChildView(root.createChildView(viewStack, { id: props.id }));
        root.ref.image = null;
    },
    write: createRoute({
        DID_LOAD_IMAGE: ({ root, props }) => {
            root.ref.image = root.appendChildView(root.createChildView(image, { id: props.id }));
        }
    }, ({ root }) => {
        const { image: image$$1, viewStack: viewStack$$1 } = root.ref;
        if (!image$$1) return;
        const top = root.rect.element.top;
        viewStack$$1.offsetTop = top;
        image$$1.offsetTop = top;
    })
});

const updateResizeButton = (button$$1, fraction) => {
    button$$1.element.dataset.scaleDirection = fraction === null || fraction > 1 ? 'up' : 'down';
};

const editUtils = createView({
    name: 'utils',
    create: ({ root }) => {

        // available utils
        const availableUtils = {
            //#if !_EXCLUDE_CROP_UTIL
            'crop': {
                title: root.query('GET_LABEL_BUTTON_UTIL_CROP'),
                icon: createIcon(`<g fill="none" fill-rule="evenodd" stroke-linecap="round" stroke-linejoin="round" stroke="currentColor" stroke-width="2"><path d="M23 17H9a2 2 0 0 1-2-2v-5m0-3V1"/><path d="M1 7h14a2 2 0 0 1 2 2v7m0 4v3"/></g>`)
            },
            //#endif
            //#if !_EXCLUDE_FILTER_UTIL
            'filter': {
                title: root.query('GET_LABEL_BUTTON_UTIL_FILTER'),
                icon: createIcon(`<g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18.347 9.907a6.5 6.5 0 1 0-1.872 3.306M3.26 11.574a6.5 6.5 0 1 0 2.815-1.417"/><path d="M10.15 17.897A6.503 6.503 0 0 0 16.5 23a6.5 6.5 0 1 0-6.183-8.51"/></g>`)
            },
            //#endif
            //#if !_EXCLUDE_COLOR_UTIL
            'color': {
                title: root.query('GET_LABEL_BUTTON_UTIL_COLOR'),
                icon: createIcon(`<g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 1v5.5m0 3.503V23M12 1v10.5m0 3.5v8M20 1v15.5m0 3.5v3M2 7h4M10 12h4M18 17h4"/></g>`)
            },
            //#endif
            //#if !_EXCLUDE_MARKUP_UTIL
            'markup': {
                title: root.query('GET_LABEL_BUTTON_UTIL_MARKUP'),
                icon: createIcon(`<g fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.086 2.914a2.828 2.828 0 1 1 4 4l-14.5 14.5-5.5 1.5 1.5-5.5 14.5-14.5z"/></g>`)
            },
            //#endif
            //#if !_EXCLUDE_RESIZE_UTIL
            'resize': {
                title: root.query('GET_LABEL_BUTTON_UTIL_RESIZE'),
                icon: createIcon(`<g fill="none" fill-rule="evenodd" stroke-width="2" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="12" width="10" height="10" rx="2"/><path d="M4 11.5V4a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-5.5"/><path d="M14 10l3.365-3.365M14 6h4v4" class="doka--icon-resize-arrow-ne"/><path d="M14 10l3.365-3.365M14 6v4h4" class="doka--icon-resize-arrow-sw"/></g>`)
            }
            //#endif
        };

        root.ref.utils = Object.keys(availableUtils).map(id => ({
            id, ...availableUtils[id]
        }));

        root.ref.utilMenuRequiredWidth = null;
    },
    read: ({ root }) => {
        if (root.ref.utilMenuRequiredWidth === null) {
            const width = root.childViews.reduce((prev, view) => prev + view.rect.outer.width, 0);
            root.ref.utilMenuRequiredWidth = width === 0 ? null : width;
        }
    },
    write: createRoute({

            DID_SET_UTILS: ({ root }) => {

                const activeUtils = [...root.query('GET_UTILS')];

                // remove current utils
                root.childViews.forEach(childView => root.removeChildView(childView));

                // update count
                root.element.dataset.utilCount = activeUtils.length;
                if (activeUtils.length === 1) {
                    activeUtils.length = 0;
                }

                // add new utils
                activeUtils.forEach(utilId => {

                    const util = root.ref.utils.find(util => util.id === utilId);

                    const view = root.appendChildView(root.createChildView(button, {
                        name: 'tab',
                        view: button,
                        label: util.title,
                        opacity: 1,
                        icon: util.icon,
                        id: util.id,
                        action: () => root.dispatch('CHANGE_VIEW', { id: util.id })
                    }));

                    root.ref[`util_button_${util.id}`] = view;
                });

            },

            SHOW_VIEW: ({ root, action }) => {
                root.childViews.forEach(view => {
                    view.element.dataset.active = view.id === action.id;
                });
            },

        }, ({ root, props, timestamp }) => {

            const view = root.query('GET_CROP', props.id, timestamp);
            if (!view) return;

            const { cropStatus } = view;

            if (root.ref.util_button_resize) {
                updateResizeButton(root.ref.util_button_resize, cropStatus.image.width ? cropStatus.image.width / cropStatus.crop.width : null);
            }

            root.element.dataset.layout = root.ref.utilMenuRequiredWidth > root.rect.element.width ? 'compact' : 'spacious';

        })

});

const HAS_WEBGL = isBrowser() && (() => {
    try {
        const params = {
            antialias: false,
            alpha: false
        };
        const canvas = document.createElement('canvas'); 
        return !!window.WebGLRenderingContext && (canvas.getContext('webgl', params) || canvas.getContext('experimental-webgl', params));
    }
    catch(e) {
        return false;
    }
})();

const hasWebGL = () => HAS_WEBGL;

const editContainer = createView({
    name: 'container',

    // creates the container contents
    create:({ root }) => {

        const controls = [];

        if (root.query('GET_ALLOW_BUTTON_RESET')) {
            controls.push({
                view: button,
                opacity: 0,
                label: root.query('GET_LABEL_BUTTON_RESET'),
                didCreateView: view => root.ref.btnReset = view,
                name: 'app action-reset icon-only',
                icon: createIcon('<g fill="currentColor" fill-rule="nonzero"><path d="M6.036 13.418L4.49 11.872A.938.938 0 1 0 3.163 13.2l2.21 2.209a.938.938 0 0 0 1.326 0l2.209-2.21a.938.938 0 0 0-1.327-1.326l-1.545 1.546zM12 10.216a1 1 0 0 1 2 0V13a1 1 0 0 1-2 0v-2.784z"/><path d="M15.707 14.293a1 1 0 0 1-1.414 1.414l-2-2a1 1 0 0 1 1.414-1.414l2 2z"/><path d="M8.084 19.312a1 1 0 0 1 1.23-1.577 6 6 0 1 0-2.185-3.488 1 1 0 0 1-1.956.412 8 8 0 1 1 2.912 4.653z"/></g>', 26),
                action: () => root.dispatch('EDIT_RESET')
            });
        }

        if (root.query('GET_ALLOW_BUTTON_CANCEL')) {
            controls.unshift({
                view: button,
                label: root.query('GET_LABEL_BUTTON_CANCEL'),
                name: 'app action-cancel icon-fallback',
                opacity: 1,
                icon: createIcon('<g fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6L6 18M6 6l12 12"/></g>'),
                didCreateView:(view) => {
                    root.ref.btnCancel = view;
                },
                action: () => {
                    root.dispatch('EDIT_CANCEL');
                }
            });
        }

        // add tools
        controls.push({
            view: editUtils
        });

        if (root.query('GET_ALLOW_BUTTON_CONFIRM')) {
            controls.push({
                view: button,
                label: root.query('GET_LABEL_BUTTON_CONFIRM'),
                name: 'app action-confirm icon-fallback',
                opacity: 1,
                icon: createIcon('<polyline fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="20 6 9 17 4 12"></polyline>'),
                didCreateView:(view) => {
                    root.ref.btnConfirm = view;
                },
                action: () => {
                    root.dispatch('EDIT_CONFIRM');
                }
            });
        }

        root.ref.menu = root.appendChildView(root.createChildView(createGroup('menu'), { controls } ));
        root.ref.menu.opacity = 0;

        root.ref.status = root.appendChildView(root.createChildView(editStatus));

        root.ref.hasWebGL = hasWebGL();
        if (!root.ref.hasWebGL) {
            root.dispatch('MISSING_WEBGL');
        }
        else {
            root.dispatch('AWAIT_IMAGE');
        }

        root.ref.handleFocusOut = e => {

            // if no new target
            if (!e.relatedTarget) return;

            // if target is not contained in Doka, don't move focus
            if (!contains(root.element, e.relatedTarget)) return;

            const { status } = root.ref;
            if (status.element.dataset.viewStatus === 'busy') status.element.focus();
        };

        root.ref.handleFocusIn = e => {

            const { menu, content } = root.ref;
            const target = e.target;

            // if is in menu, ignore
            if (menu.element.contains(target)) return;
            
            // if is in inactive element move to next active element
            if (content && content.element.contains(target)) {
                const views = Array.from(root.element.querySelectorAll('[data-view-active=false]'));
                const isHiddenTarget = views.reduce((prev, view) => {
                    if (view.contains(target)) {
                        prev = true;
                    }
                    return prev;
                }, false);

                // valid target as it's not hidden from view
                if (!isHiddenTarget) return;
                
                // move to menu
                const safeTarget = menu.element.querySelector('button,input,[tabindex]');
                safeTarget.focus();
            }

        };
        
        root.element.addEventListener('focusin', root.ref.handleFocusIn);
        root.element.addEventListener('focusout', root.ref.handleFocusOut);

        root.ref.previousState = null;
    },
    destroy:({ root }) => {
        root.element.removeEventListener('focusin', root.ref.handleFocusIn);
        root.element.removeEventListener('focusout', root.ref.handleFocusOut);
    },
    write: createRoute({

        UNLOAD_IMAGE: ({ root }) => {
            if (!root.ref.content) return;
            root.ref.content.opacity = 0;
            root.ref.menu.opacity = 0;
        },

        DID_UNLOAD_IMAGE: ({ root }) => {
            root.removeChildView(root.ref.content);
            root.ref.content = null;
        },

        DID_LOAD_IMAGE: ({ root, props }) => {
            
            if (!root.ref.hasWebGL) return;

            // append content to edit the image
            root.ref.content = root.appendChildView(
                root.createChildView(editContent, {
                    opacity: null,
                    id: props.id
                })
            );

            // show main menu controls
            root.ref.menu.opacity = 1;

        },

        SHOW_VIEW: ({ root, action }) => {

            // if is resize view limit image rendering
            root.element.dataset.limitOverflow = action.id === 'resize';
        }

    },({ root, props, timestamp }) => {

        const view = root.query('GET_CROP', props.id, timestamp);
        if (!view) return;

        const cropStatus = view.cropStatus;
        const cropProps = cropStatus.props;
        const currentState = {
            crop: {
                center: {
                    x: roundFloat(cropProps.center.x, 5),
                    y: roundFloat(cropProps.center.y, 5)
                },
                rotation: roundFloat(cropProps.rotation, 5),
                zoom: roundFloat(cropProps.zoom, 5),
                aspectRatio: roundFloat(cropProps.aspectRatio, 5),
                flip: {
                    horizontal: cropProps.flip.horizontal,
                    vertical: cropProps.flip.vertical
                },
                scaleToFit: cropProps.scaleToFit,
                width: cropStatus.currentWidth,
                height: cropStatus.currentHeight
            },
            preview: {
                scale: view.scale / Math.max(view.cropRect.width / view.previewSize.width, view.cropRect.height / view.previewSize.height)
            }
        };

        if (hasStateChanged(root.ref.previousState, currentState)) {

            root.dispatch('DID_UPDATE', {
                state: {
                    ...currentState
                }
            });

            // remember previous state
            root.ref.previousState = currentState;
        }
        
        // update interface
        const { btnReset, btnCancel, content } = root.ref;
        const { canReset } = view;

        if (btnReset) {
            btnReset.opacity = canReset ? 1 : 0;
        }

        if (btnCancel && root.query('GET_UTILS').length > 1 && btnReset) {
            const rootSize = root.query('GET_ROOT_SIZE');
            // small views the cancel and reset buttons are positioned on top of each other
            btnCancel.opacity = canReset && rootSize.width <= 600 ? 0 : 1;
        }

        if (content && content.opacity === 0) {
            root.dispatch('DID_UNLOAD_IMAGE');
        }
        
    })
});

const hasStateChanged = (previous, current) => {

    if (!previous) return true;

    const prevCrop = previous.crop;
    const currCrop = current.crop;

    // compare
    return (
       (prevCrop.width !== currCrop.width) ||
       (prevCrop.height !== currCrop.height) ||
       (prevCrop.center.x !== currCrop.center.x) ||
       (prevCrop.center.y !== currCrop.center.y) ||
       (prevCrop.rotation !== currCrop.rotation) ||
       (prevCrop.scaleToFit !== currCrop.scaleToFit) || 
       (prevCrop.zoom !== currCrop.zoom) || 
       (prevCrop.aspectRatio !== currCrop.aspectRatio) ||
       (prevCrop.flip.horizontal !== currCrop.flip.horizontal) ||
       (prevCrop.flip.vertical !== currCrop.flip.vertical)
    )
};

const createPointerEvents = (element) => {

    const api =  { destroy: () => {} };

    if ('onpointerdown' in window || element.pointersPolyfilled) return api;

    element.pointersPolyfilled = true;

    let totalActivePointers = 0;
    const activePointers = [];

    const dispatch = (target, props, capture) => {

        const event = new UIEvent(
            props.type, {
                view: window,
                bubbles: !capture
            }
        );
        
        Object.keys(props).forEach(key => {
            Object.defineProperty(event, key, {
				value: props[key],
				writable: false
			});
        });
        
        target.dispatchEvent(event);
    };

    /**
     * Touch
     */
    const createPointerFromTouch = (type, event, capture) => {
        return Array.from(event.changedTouches).map(touch => {
            const origin = activePointers[touch.identifier];
            const pointer = {
                type,
                pageX: touch.pageX,
                pageY: touch.pageY,
                pointerId: touch.identifier,
                isPrimary: origin ? origin.isPrimary : totalActivePointers === 0,
                preventDefault: () => event.preventDefault()
            };
            dispatch(touch.target, pointer, capture);
            return pointer;
        });
    };

    const handleTouchStart = e => {
        const pointers = createPointerFromTouch('pointerdown', e);
        pointers.forEach(pointer => {
            activePointers[pointer.pointerId] = pointer;
            totalActivePointers++;
        });
    };

    const handleTouchMove = e => {
        createPointerFromTouch('pointermove', e);
    };

    const handleTouchEnd = e => {
        const pointers = createPointerFromTouch('pointerup', e);
        pointers.forEach(pointer => {
            delete activePointers[pointer.pointerId];
            totalActivePointers--;
        });
    };

    /**
     * Mouse
     */
    const createPointerFromMouse = (type, event, capture) => {
        const pointer = {
            type,
            pageX: event.pageX,
            pageY: event.pageY,
            pointerId: 0,
            isPrimary: true,
            preventDefault: () => event.preventDefault()
        };
        dispatch(event.target, pointer, capture);
        return pointer;
    };

    const handleMouseStart = e => {
        createPointerFromMouse('pointerdown', e);
    };

    const handleMouseMove = e => {
        createPointerFromMouse('pointermove', e);
    };

    const handleMouseEnd = e => {
        createPointerFromMouse('pointerup', e);
    };
    
    if ('ontouchstart' in window) {
        element.addEventListener('touchstart', handleTouchStart);
        element.addEventListener('touchmove', handleTouchMove);
        element.addEventListener('touchend', handleTouchEnd);
    }
    else if ('onmousedown' in window) {
        element.addEventListener('mousedown', handleMouseStart);
        element.addEventListener('mousemove', handleMouseMove);
        element.addEventListener('mouseup', handleMouseEnd);
    }

    api.destroy = () => {
        activePointers.length = 0;
        element.pointersPolyfilled = false;
        element.removeEventListener('touchstart', handleTouchStart);
        element.removeEventListener('touchmove', handleTouchMove);
        element.removeEventListener('touchend', handleTouchEnd);
        element.removeEventListener('mousedown', handleMouseStart);
        element.removeEventListener('mousemove', handleMouseMove);
        element.removeEventListener('mouseup', handleMouseEnd);
    };

    return api;
};

const prevent = e => {

    // prevent zoom gesture
    if (e.type === 'gesturestart') {
        e.preventDefault();
        return;
    }

    // test if not scrollable container
    const isTargetInScrollContainer = climb(e.target, el => el.isScrollContainer);
    if (isTargetInScrollContainer) return;

    // no scroll container, prevent
    e.preventDefault();
};

const editor = createView({
    name: 'editor',
    ignoreRect: true,
    mixins: {
        styles: [
            'opacity'
        ],
        animations: {
            opacity: { type: 'tween', duration: 350 }
        },
        apis: [
            'markedForRemoval'
        ]
    },
    create:({ root, props }) => {

        // obviously not marked for removal by default
        props.markedForRemoval = false;

        // prevent scrolling and zooming on iOS (at least on 10.x)
        if (isIOS()) {
            // scrolling
            root.element.addEventListener('touchmove', prevent, { passive: false });

            // zooming
            root.element.addEventListener('gesturestart', prevent);
        }

        // fill pointerevents
        root.ref.pointerPolyfill = createPointerEvents(root.query('GET_POINTER_EVENTS_POLYFILL_SCOPE') === 'root' ? root.element : document.documentElement);

        // add main editor container node
        root.appendChildView(root.createChildView(editContainer, { ...props }));
    },
    destroy: ({ root }) => {

        root.ref.pointerPolyfill.destroy();

        root.element.removeEventListener('touchmove', prevent, true);
        root.element.removeEventListener('gesturestart', prevent);

    }
});

const createTouchDetector = () => {
    
    function firstTouchDetected() {
        api.fire('touch-detected');
        window.removeEventListener('touchstart', firstTouchDetected, false);
    }

    const api = {
        ...on(),
        destroy: () => {
            window.removeEventListener('touchstart', firstTouchDetected, false);
        }
    };

    window.addEventListener('touchstart', firstTouchDetected, false);
    return api;
};

const createFileCatcher = (element) => {

    const state = {
        browseEnabled: false
    };

    let input;

    const handleInputChange = () => {
        if (!input.files.length) return;
        api.fire('drop', Array.from(input.files));
    };

    const api = {
        ...on(),
        enableBrowse: () => {
            if (state.browseEnabled) return;

            input = document.createElement('input');
            input.style.display = 'none';
            input.setAttribute('type', 'file');
            input.addEventListener('change', handleInputChange);
            element.appendChild(input);
            element.addEventListener('click', handleClick);

            state.browseEnabled = true;
        },
        disableBrowse: () => {
            if (!state.browseEnabled) return;
            
            input.removeEventListener('change', handleInputChange);
            input.parentNode.removeChild(input);
            element.removeEventListener('click', handleClick);

            state.browseEnabled = false;
        },
        destroy: () => {
            element.removeEventListener('dragover', handleDragOver);
            element.removeEventListener('drop', handleDrop);
            element.removeEventListener('click', handleClick);

            if (!input) return;
            input.removeEventListener('change', handleInputChange);
        }
    };

    // click file input
    const handleClick = () => input.click();
    
    // need to block dragover to allow drop
    const handleDragOver = e => e.preventDefault();
    
    // deal with dropped items,
    const handleDrop = e => {
        e.preventDefault();
        const files = Array.from(e.dataTransfer.items || e.dataTransfer.files)
            .map(item => item.getAsFile && item.kind === 'file' ? item.getAsFile() : item);
        api.fire('drop', files);
    };

    // listen to events for dragging and dropping
    element.addEventListener('dragover', handleDragOver);
    element.addEventListener('drop', handleDrop);

    return api;
};

const createFocusTrap = (element) => {

    const handleFocus = e => {

        // not interested if pressed key is not is tab key
        if (e.keyCode !== 9) return;

        const focusables = Array.from(element.querySelectorAll('button,input,[tabindex]'))
            .filter(control => 
                control.style.visibility !== 'hidden' && 
                control.tabIndex !== -1
            );
        const firstFocusable = focusables[0];
        const lastFocusable = focusables[focusables.length-1];
        
        if (e.shiftKey) {
            if (document.activeElement === firstFocusable) {
                lastFocusable.focus();
                e.preventDefault();
            }
        }
        else {
            if (document.activeElement === lastFocusable) {
                firstFocusable.focus();
                e.preventDefault();
            }
        }

    };

    element.addEventListener('keydown', handleFocus);

    return {
        destroy: () => {
            element.removeEventListener('keydown', handleFocus);
        }
    }
};

const isFullscreen = (root) => root.ref.isFullscreen;

const shouldBeFullscreen = (root) => /fullscreen/.test(root.query('GET_STYLE_LAYOUT_MODE'));

const isFloating = (root) => /fullscreen|preview/.test(root.query('GET_STYLE_LAYOUT_MODE'));

const isModal = (root) => /modal/.test(root.query('GET_STYLE_LAYOUT_MODE'));

const mayBeAutoClosed = (root) => root.query('GET_ALLOW_AUTO_CLOSE');

const canBeAutoClosed = isFloating;
const canBeClosed = isFloating;

const updateStyleViewport = (root) => {
    const { environment, isSingleUtil, canBeControlled } = root.ref;
    root.element.dataset.styleViewport = getViewportBySize(root.rect.element.width, root.rect.element.height) + ' ' + environment.join(' ') + (isSingleUtil ? ' single-util' : ' multi-util') + (canBeControlled ? ' flow-controls' : ' no-flow-controls');
};

const setupFullscreenMode = (root) => {

    const { element } = root;
    const { handleFullscreenUpdate, handleEscapeKey } = root.ref;

    // focusable by javascript
    element.setAttribute('tabindex', -1);

    // set fullscreen style
    handleFullscreenUpdate();

    // set up focus trap
    root.ref.focusTrap = createFocusTrap(element);

    // set up escape listener
    element.addEventListener('keydown', handleEscapeKey);

    // update fullscreen status
    window.addEventListener('resize', handleFullscreenUpdate);

    // paint custom scrollbars if visible
    const hasVisibleScrollbar = window.innerWidth - document.documentElement.clientWidth > 0;
    if (hasVisibleScrollbar) {
        document.body.classList.add('doka--parent');
    }
    
    // add ourselves to the document body
    document.body.appendChild(element);
    
    // head viewport value
    let viewportElement = document.querySelector('meta[name=viewport]');
    root.ref.defaultViewportContent = viewportElement ? viewportElement.getAttribute('content') : null;
    if (!viewportElement) {
        viewportElement = document.createElement('meta');
        viewportElement.setAttribute('name', 'viewport');
        document.head.appendChild(viewportElement);
    }
    viewportElement.setAttribute('content', 'width=device-width, height=device-height, initial-scale=1, maximum-scale=1, user-scalable=0');

    // reveal root view
    root.opacity = 1;

    // focus editor if not already focussed
    if (!root.element.contains(document.activeElement)) {
        element.focus();
    }
    
    // redraw view
    root.dispatch('INVALIDATE_VIEWPORT');

    root.ref.isFullscreen = true;
};

const cleanFullscreenMode = (root) => {

    const { element } = root;
    const { handleFullscreenUpdate, focusTrap, handleEscapeKey } = root.ref;

    // no longer focusable by javascript
    element.removeAttribute('tabindex');

    // remove focus trap
    focusTrap.destroy();

    // remove escape listener
    element.removeEventListener('keydown', handleEscapeKey);

    // stop resize listener
    window.removeEventListener('resize', handleFullscreenUpdate);

    document.body.classList.remove('doka--parent');

    let viewportElement = document.querySelector('meta[name=viewport]');
    if (root.ref.defaultViewportContent) {
        viewportElement.setAttribute('content', root.ref.defaultViewportContent);
        root.ref.defaultViewportContent = null;
    }
    else {
        viewportElement.parentNode.removeChild(viewportElement);
    }

    root.ref.isFullscreen = false;
};

const root = createView({
    name: 'root',
    ignoreRect: true,
    mixins: {
        styles: [
            'opacity'
        ],
        animations: {
            opacity: { type: 'tween', duration: 350 }
        }
    },
    create:({ root, props }) => {

        root.element.id = root.query('GET_ID') || `doka-${props.id}`;

        const className = root.query('GET_CLASS_NAME');
        if (className) {
            root.element.classList.add(className);
        }

        root.ref.environment = [];

        root.ref.shouldBeDestroyed = false;
        root.ref.isClosing = false;
        root.ref.isClosed = false;
        root.ref.isFullscreen = false;

        // catch files
        if (root.query('GET_ALLOW_DROP_FILES')) {
            root.ref.catcher = createFileCatcher(root.element);
            root.ref.catcher.on('drop', files => {
                files.forEach(file => {
                    root.dispatch('REQUEST_LOAD_IMAGE', { source:file });
                });
            });
        }

        // detect touch
        root.ref.touchDetector = createTouchDetector();
        root.ref.touchDetector.onOnce('touch-detected', () => {
            root.ref.environment.push('touch');
        });

        // add editor
        root.ref.editor = root.appendChildView(
            root.createChildView(editor, { 
                id: props.id
            })
        );

        // apply initial style properties
        root.query('GET_STYLES').filter(style => !isEmpty(style.value))
            .map(({ name, value }) => {
                root.element.dataset[name] = value;
            });

        // trigger redraws on window resize
        root.ref.updateViewport = () => {
            root.dispatch('INVALIDATE_VIEWPORT');
        };
        window.addEventListener('resize', root.ref.updateViewport);
        window.addEventListener('scroll', root.ref.updateViewport);

        // detect enabled tools
        root.ref.isSingleUtil = root.query('GET_UTILS').length === 1;

        // detect enabled buttons
        root.ref.canBeControlled = root.query('GET_ALLOW_BUTTON_CONFIRM') || root.query('GET_ALLOW_BUTTON_CANCEL');
        
        // update viewport styles
        updateStyleViewport(root);
        
        // set up measure, this will be used in first read to determine page height and bottom padding
        const measure = document.createElement('div');
        measure.style.cssText = 'position:fixed;height:100vh;top:0;';
        root.ref.measure = measure;
        document.body.appendChild(measure);

        // Define functions used in fullscreen mode
        root.ref.handleEscapeKey = e => {
            // if not is escape key
            if (e.keyCode !== 27) return;
            root.dispatch('EDIT_CANCEL');
        };

        // determines the fullscreen height to use
        root.ref.initialScreenMeasureHeight = null;
        root.ref.handleFullscreenUpdate = () => {
            root.element.dataset.styleFullscreen = window.innerHeight === root.ref.initialScreenMeasureHeight;
        };

        // root rectangle
        root.ref.clientRect = { left: 0, top: 0 };

        // listen for clicks on root if in modal mode
        if (isModal(root)) {
            root.ref.handleModalTap = e => {
                if (e.target !== root.element) return;
                root.dispatch('EDIT_CANCEL');
            };
            root.element.addEventListener('pointerdown', root.ref.handleModalTap);
        }
    },
    read:({ root }) => {

        const { measure } = root.ref;

        // do we need to measure the height of the screen to correct on iOS
        if (measure) {
            root.ref.initialScreenMeasureHeight = measure.offsetHeight;
            measure.parentNode.removeChild(measure);
            root.ref.measure = null;
        }

        // measure position on page
        root.ref.clientRect = root.element.getBoundingClientRect();
        root.ref.clientRect.leftScroll = root.ref.clientRect.left + (window.scrollX || window.pageXOffset);
        root.ref.clientRect.topScroll = root.ref.clientRect.top + (window.scrollY || window.pageYOffset);
    },
    write: createRoute({
        ENTER_FULLSCREEN: ({ root }) => {
            setupFullscreenMode(root);
        },
        EXIT_FULLSCREEN: ({ root }) => {
            cleanFullscreenMode(root);
            // TODO: return to original parent element if was defined
        },
        SHOW_VIEW: ({ root, action }) => {
            root.element.dataset.view = action.id;
        },
        DID_SET_STYLE_LAYOUT_MODE: ({ root, action }) => {
            
            // set to root style so we can style the editor according to its placement
            root.element.dataset.styleLayoutMode = action.value || 'none';

            // test if has gone fullscreen
            if (/fullscreen/.test(action.value) && !/fullscreen/.test(action.prevValue)) {
                root.dispatch('ENTER_FULLSCREEN');
            }
        },
        AWAITING_IMAGE: ({ root }) => {
            if (root.ref.catcher && root.query('GET_ALLOW_BROWSE_FILES')) {
                root.ref.catcher.enableBrowse();
            }
        },
        DID_REQUEST_LOAD_IMAGE: ({ root }) => {

            if (root.ref.catcher && root.query('GET_ALLOW_BROWSE_FILES')) {
                root.ref.catcher.disableBrowse();
            }

            // reveal layer when loading a new image
            if (root.opacity === 0) {
                root.opacity = 1;
            }

            // no longer closed
            root.ref.isClosing = false;
            root.ref.isClosed = false;

            // if should be fullscreen but is not fullscreen
            if (shouldBeFullscreen(root) && !isFullscreen(root)) {
                root.dispatch('ENTER_FULLSCREEN');
                return;
            }

            // no parent? decide to go fullscreen automatically
            const layoutMode = root.query('GET_STYLE_LAYOUT_MODE');
            if ((layoutMode === null || layoutMode === 'modal') && !root.element.parentNode) {
                root.dispatch('SET_STYLE_LAYOUT_MODE', { value: ('fullscreen ' + (layoutMode || '')).trim() });
            }

            // prevent clicking to load new image

        },
        DID_CANCEL: ({ root }) => {
            if (canBeAutoClosed(root) && mayBeAutoClosed(root)) {
                root.dispatch('EDIT_CLOSE');
            }
        },
        DID_CONFIRM: ({ root }) => {
            if (canBeAutoClosed(root) && mayBeAutoClosed(root)) {
                root.dispatch('EDIT_CLOSE');
            }
        },
        EDIT_CLOSE: ({ root }) => {

            if (!canBeClosed(root)) return;

            root.opacity = root.opacity || 1;
            root.opacity = 0;
            root.ref.isClosed = false;
            root.ref.isClosing = true;

            if (root.query('GET_ALLOW_AUTO_DESTROY')) {
                root.ref.shouldBeDestroyed = true;
            }

            if (isFullscreen(root)) {
                root.dispatch('EXIT_FULLSCREEN');
            }

        },
        DID_SET_UTILS: ({ root }) => {
            root.ref.isSingleUtil = root.query('GET_UTILS').length === 1;
        }
    }, ({ root }) => {

        // update viewport layout so we can adjust styles accordingly
        updateStyleViewport(root);

        // test if root rect is different from element rect, if so, the window has been resized and it's time for an updated rectangle
        const rootRect = root.query('GET_ROOT');
        const elementRect = root.rect.element;

        if (rootRect.width !== elementRect.width || 
            rootRect.height !== elementRect.height || 
            rootRect.y !== root.ref.clientRect.top ||
            rootRect.topScroll !== root.ref.clientRect.topScroll) {
            
            root.dispatch('UPDATE_ROOT_RECT', {
                rect: {
                    x: root.ref.clientRect.left,
                    y: root.ref.clientRect.top,
                    left: root.ref.editor.rect.element.left,
                    top: root.ref.editor.rect.element.top,
                    leftScroll: root.ref.clientRect.leftScroll,
                    topScroll: root.ref.clientRect.topScroll,
                    width: root.rect.element.width,
                    height: root.rect.element.height,
                }
            });
        }

    }),

    // test if should auto remove editor from view
    didWriteView: ({ root }) => {

        const { isClosed, isClosing, shouldBeDestroyed } = root.ref;

        if (!isClosed && isClosing && root.opacity === 0) {
            
            root.dispatch('DID_CLOSE');
            root.ref.isClosed = true;
            root.ref.isClosing = false;

            if (shouldBeFullscreen(root) && root.element.parentNode) {
                document.body.removeChild(root.element);
            }

            if (shouldBeDestroyed) {
                root.dispatch('EDIT_DESTROY');
            }
        }
    },
    destroy: ({ root }) => {

        if (isFullscreen(root)) {
            cleanFullscreenMode(root);
        }

        if (isModal(root)) {
            root.element.removeEventListener('pointerdown', root.ref.handleModalTap);
        }

        if (shouldBeFullscreen(root) && root.element.parentNode) {
            document.body.removeChild(root.element);
        }

        window.removeEventListener('resize', root.ref.updateViewport);

        root.ref.touchDetector.destroy();

        if (root.ref.catcher) {
            root.ref.catcher.destroy();
        }

    },
});

const getViewportBySize = (width, height) => {

    let viewport = '';

    if (width === 0 && height === 0) {
        return 'detached';
    }
    
    viewport += height > width ? 'portrait' : 'landscape';
    
    if (width <= 600) {
        viewport += ' x-cramped';
    }
    else if (width <= 1000) {
        viewport += ' x-comfortable';
    }
    else {
        viewport += ' x-spacious';
    }

    return viewport.trim();
};

// creates the app
const createApp = (initialOptions = {}) => {

    // get default options
    const defaultOptions$$1 = getOptions();

    // create the data store, this will contain all our app info
    const store = createStore(

        // initial state (should be serializable)
        createInitialState(defaultOptions$$1),

        // queries
        [queries, createOptionQueries(defaultOptions$$1)],

        // action handlers
        [actions, createOptionActions(defaultOptions$$1)]
    );

    // set initial options
    store.dispatch('SET_OPTIONS', { options: initialOptions });

    // kick thread if visibility changes
    const visibilityHandler = () => {
        if (document.hidden) return;
        store.dispatch('KICK');
    };
    document.addEventListener('visibilitychange', visibilityHandler);

    // our unique id
    const id = getUniqueId();
    store.dispatch('SET_UID', { id });

    // remember the element the app is bound to, if any
    let originalElement = null;

    // base view reference
    const view = root(store, { id });

    //
    // PRIVATE API -------------------------------------------------------------------------------------
    //
    let resting = false;

    const readWriteApi = {
        // necessary for update loop

        /**
         * Reads from dom (never call manually)
         * @private
         */
        _read: () => {

            // if resting, no need to read as numbers will still all be correct
            if (resting) return;

            // read view data
            view._read();
    
        },

        /**
         * Writes to dom (never call manually)
         * @private
         */
        _write: ts => {
            
            // get all actions from store
            const actions$$1 = store
                .processActionQueue()

                // filter out set actions (these will automatically trigger DID_SET)
                .filter(action => !/^SET_/.test(action.type));

            // if was idling and no actions stop here
            if (resting && !actions$$1.length) return;

            // some actions might trigger events
            routeActionsToEvents(actions$$1);

            // update the view
            resting = view._write(ts, actions$$1);

            // now idling
            if (resting) {
                store.processDispatchQueue();
            }

            // clean up!
            if (actions$$1.find(action => action.type === 'EDIT_DESTROY')) {
                destroy();
            }

        }
    };

    //
    // EXPOSE EVENTS -------------------------------------------------------------------------------------
    //
    const createEvent = name => data => {

        const event = {
            type: name
        };

        if (!data) return event;

        if (data.hasOwnProperty('error')) {
            event.error = isObject(data.error) ? { ...data.error } : data.error || null;
        }

        if (data.hasOwnProperty('output')) {
            event.output = data.output;
        }

        if (data.hasOwnProperty('image')) {
            event.image = data.image;
        }

        if (data.hasOwnProperty('source')) {
            event.source = data.source;
        }

        if (data.hasOwnProperty('state')) {
            event.state = data.state;
        }

        return event;
    };

    const eventRoutes = {
        DID_CONFIRM: createEvent('confirm'),
        DID_CANCEL: createEvent('cancel'),
        DID_REQUEST_LOAD_IMAGE: createEvent('loadstart'),
        DID_LOAD_IMAGE: createEvent('load'),
        DID_LOAD_IMAGE_ERROR: createEvent('loaderror'),
        DID_SHOW_IMAGE: createEvent('ready'),
        DID_UPDATE: createEvent('update'),
        DID_CLOSE: createEvent('close'),
        DID_DESTROY: createEvent('destroy'),
        DID_INIT: createEvent('init')
    };

    const exposeEvent = event => {

        // create event object to be dispatched
        const detail = { doka: exports, ...event };
        delete detail.type;
        if (view) {
            view.element.dispatchEvent(
                new CustomEvent(`Doka:${event.type}`, {
                    // event info
                    detail,
    
                    // event behaviour
                    bubbles: true,
                    cancelable: true,
                    composed: true // triggers listeners outside of shadow root
                })
            );
        }

        // event object to params used for `on()` event handlers and callbacks `oninit()`
        const params = [];

        // if is possible error event, make it the first param
        if (event.hasOwnProperty('error')) {
            params.push(event.error);
        }

        // append other props
        const filtered = ['type', 'error'];
        Object.keys(event)
            .filter(key => !filtered.includes(key))
            .forEach(key => params.push(event[key]));

        // on(type, () => { })
        exports.fire(event.type, ...params);

        // oninit = () => {}
        const handler = store.query(`GET_ON${event.type.toUpperCase()}`);
        if (handler) {
            handler(...params);
        }
    };

    const routeActionsToEvents = actions$$1 => {
        if (!actions$$1.length) {
            return;
        }

        actions$$1.forEach(action => {
            if (!eventRoutes[action.type]) {
                return;
            }
            const routes = eventRoutes[action.type];
            (Array.isArray(routes) ? routes : [routes]).forEach(route => {
                setTimeout(() => {
                    exposeEvent(route(action.data));
                }, 0);
            });
        });
    };
    


    //
    // PUBLIC API -------------------------------------------------------------------------------------
    //
    const setOptions$$1 = options => store.dispatch('SET_OPTIONS', { options });

    const destroy = () => {

        // request destruction from update loop
        exports.fire('destroy', view.element);

        // stop listening to the visiblitychange event
        document.removeEventListener('visibilitychange', visibilityHandler);

        // destroy view
        view._destroy();

        // dispatch destroy
        store.dispatch('DID_DESTROY');
    };

    const exports = {
        // supports events
        ...on(),

        // inject private api methods
        ...readWriteApi,

        // inject all getters and setters
        ...createOptionAPI(store, defaultOptions$$1),

        /**
         * Override options defined in options object
         * @param options
         */
        setOptions: setOptions$$1,


        /**
         * Gets current file crop information
         */
        setData:(options) => {
            store.dispatch('SET_DATA', options);
        },

        getData:(options) => new Promise((resolve, reject) => {
            store.dispatch('GET_DATA', {
                ...options,
                success: resolve, 
                failure: reject
            });
        }),

        /**
         * Loads a file to the editor
         */
        open: (source, options = {}) => new Promise((resolve, reject) => {

            if (!source) return;

            store.dispatch('REQUEST_LOAD_IMAGE', { 
                source,
                options,
                success: resolve,
                failure: reject,
                resolveOnConfirm: options ? options.resolveOnConfirm : false
            });

        }),

        edit: (source, options) => exports.open(source, {...options, resolveOnConfirm: true}),

        save:(options) => new Promise((resolve, reject) => {
            store.dispatch('GET_DATA', {
                ...options,
                success: resolve, 
                failure: reject
            });
        }),

        clear: () => store.dispatch('REQUEST_REMOVE_IMAGE'),
        
        close: () => store.dispatch('EDIT_CLOSE'),

        interact: (action, value) => {
            if (action !== 'scale') return;
            store.dispatch('CROP_IMAGE_RESIZE_SET', { value });
        },

        interactEnd: (action) => {
            if (action !== 'scale') return;
            store.dispatch('CROP_IMAGE_RESIZE_RELEASE');
        },

        /**
         * Destroys the app
         */
        destroy,

        /**
         * Inserts the plugin before the target element
         */
        insertBefore: element => {
            insertBefore(view.element, element);
        },

        /**
         * Inserts the plugin after the target element
         */
        insertAfter: element => {
            insertAfter(view.element, element);
        },

        /**
         * Appends the plugin to the target element
         */
        appendTo: element => {
            element.appendChild(view.element);
        },

        /**
         * Replaces an element with the app
         */
        replaceElement: element => {

            // insert the app before the element
            insertBefore(view.element, element);

            // remove the original element
            element.parentNode.removeChild(element);

            // remember original element
            originalElement = element;
        },

        /**
         * Restores the original element
         */
        restoreElement: () => {

            if (!originalElement) {
                return; // no element to restore
            }

            // restore original element
            insertAfter(originalElement, view.element);

            // remove our element
            view.element.parentNode.removeChild(view.element);

            // remove reference
            originalElement = null;
        },

        /**
         * Returns true if the app root is attached to given element
         * @param element
         */
        isAttachedTo: element => view ? view.element === element || originalElement === element : false,

        /**
         * Returns the root element
         */
        element: {
            get: () => view ? view.element : null
        }
    };

    // Done!
    store.dispatch('DID_INIT');

    // create actual api object
    return createObject(exports);
};

const createAppObject = (customOptions = {}) => {

    // base options
    const baseOptions = getOptions();

    // default options
    const defaultOptions$$1 = {};
    forin(baseOptions, (key, value) => {
        if (isString(value)) return; // skip deprecated options
        defaultOptions$$1[key] = value[0];
    });

    // set app options
    const app = createApp({
        // default options
        ...defaultOptions$$1,

        // custom options
        ...customOptions
    });

    // return the plugin instance
    return app;
};

const toCamels = (string, separator = '-') =>
    string.replace(new RegExp(`${separator}.`, 'g'), sub =>
        sub.charAt(1).toUpperCase()
    );

const lowerCaseFirstLetter = string =>
    string.charAt(0).toLowerCase() + string.slice(1);

const attributeNameToPropertyName = attributeName => toCamels(attributeName.replace(/^data-/, ''));
    

const mapObject = (object, propertyMap) => {
    // remove unwanted
    forin(propertyMap, (selector, mapping) => {
        forin(object, (property, value) => {
            // create regexp shortcut
            const selectorRegExp = new RegExp(selector);

            // tests if
            const matches = selectorRegExp.test(property);

            // no match, skip
            if (!matches) {
                return;
            }

            // if there's a mapping, the original property is always removed
            delete object[property];

            // should only remove, we done!
            if (mapping === false) {
                return;
            }

            // move value to new property
            if (isString(mapping)) {
                object[mapping] = value;
                return;
            }

            // move to group
            const group = mapping.group;
            if (isObject(mapping) && !object[group]) {
                object[group] = {};
            }
            
            object[group][
                lowerCaseFirstLetter(property.replace(selectorRegExp, ''))
            ] = value;
        });

        // do submapping
        if (mapping.mapping) {
            mapObject(object[mapping.group], mapping.mapping);
        }
    });
};

const getAttributesAsObject = (node, attributeMapping = {}) => {

    // turn attributes into object
    const attributes = [];
    forin(node.attributes, index => attributes.push(node.attributes[index]));

    const output = attributes
        .filter(attribute => attribute.name)
        .reduce((obj, attribute) => {
        
        const value = attr(
            node,
            attribute.name
        );

        obj[attributeNameToPropertyName(attribute.name)] = value === attribute.name ? true : value;
        return obj;
    }, {});

    // do mapping of object properties
    mapObject(output, attributeMapping);

    return output;
};

const createAppAtElement = (element, options = {}) => {

    // how attributes of the input element are mapped to the options for the plugin
    const attributeMapping = {
        // translate to other name
        '^class$': 'className'
    };

    // create final options object by setting options object and then overriding options supplied on element
    const mergedOptions = {...options};
    
    const attributeOptions = getAttributesAsObject(element, attributeMapping);
    
    // merge with options object
    Object.keys(attributeOptions).forEach(key => {
        if (isObject(attributeOptions[key])) {
            if (!isObject(mergedOptions[key])) {
                mergedOptions[key] = {};
            }
            Object.assign(mergedOptions[key], attributeOptions[key]);
        }
        else {
            mergedOptions[key] = attributeOptions[key];
        }
    });

    // use element instead
    if (element.nodeName === 'CANVAS' || element.nodeName === 'IMG') {
        mergedOptions.src = element.dataset.dokaSrc ? element.dataset.dokaSrc : element;
    }

    // build plugin
    const app = createAppObject(mergedOptions);

    // add to target element
    app.replaceElement(element);

    // expose
    return app;
};

// if an element is passed, we create the instance at that element, if not, we just create an up object
const createApp$1 = (...args) => isNode(args[0]) ? createAppAtElement(...args) : createAppObject(...args.filter(arg => arg));

const copyObjectPropertiesToObject = (src, target, excluded) => {
    Object.getOwnPropertyNames(src)
        .filter(property => !excluded.includes(property))
        .forEach(key =>
            Object.defineProperty(
                target,
                key,
                Object.getOwnPropertyDescriptor(src, key)
            )
        );
};

const PRIVATE_METHODS = ['fire', '_read', '_write'];

const createAppAPI = app => {
  const api = {};
  copyObjectPropertiesToObject(app, api, PRIVATE_METHODS);
  return api;
};

// feature detection used by supported() method
const isOperaMini = () => Object.prototype.toString.call(window.operamini) === '[object OperaMini]';
const hasPromises = () => 'Promise' in window;
const hasBlobSlice = () => 'slice' in Blob.prototype;
const hasCreateObjectURL = () => 'URL' in window && 'createObjectURL' in window.URL;
const hasVisibility = () => 'visibilityState' in document;
const hasTiming = () => 'performance' in window; // iOS 8.x

const supported = (() => {

    // Runs immidiately and then remembers result for subsequent calls
    const isSupported = 

        // Has to be a browser
        isBrowser() &&

        // Can't run on Opera Mini due to lack of everything
        !isOperaMini() &&

        // Require these APIs to feature detect a modern browser
        hasVisibility() &&
        hasPromises() &&
        hasBlobSlice() &&
        hasCreateObjectURL() &&
        hasTiming();

    return () => isSupported;

})();

/**
 * Plugin internal state (over all instances)
 */
const state = {
    // active app instances, used to redraw the apps and to find the later
    apps: []
};

// plugin name
const name = 'doka';

/**
 * Public Plugin methods
 */
const fn = () => {};
let OptionTypes = {};
let create$1 = fn;
let destroy = fn;
let parse = fn;
let find = fn;
let getOptions$1 = fn;
let setOptions$1 = fn;

// if not supported, no API
if (supported()) {


    // app painter, cannot be paused or stopped at the moment
    createPainter(
        () => {
            state.apps.forEach(app => app._read());
        },
        (ts) => {
            state.apps.forEach(app => app._write(ts));
        }
    );

    // fire loaded event so we know when doka is available
    const dispatch = () => {
        // let others know we have area ready
        document.dispatchEvent(
            new CustomEvent('doka:loaded', {
                detail: {
                    supported,
                    create: create$1,
                    destroy,
                    parse,
                    find,
                    setOptions: setOptions$1
                }
            })
        );

        // clean up event
        document.removeEventListener('DOMContentLoaded', dispatch);
    };

    if (document.readyState !== 'loading') {
        // move to back of execution queue, FilePond should have been exported by then
        setTimeout(() => dispatch(), 0);
    } else {
        document.addEventListener('DOMContentLoaded', dispatch);
    }

    // updates the OptionTypes object based on the current options
    const updateOptionTypes = () => forin(getOptions(), (key, value) => {
        OptionTypes[key] = value[1];
    });

    OptionTypes = {};
    updateOptionTypes();

    // create method, creates apps and adds them to the app array
    create$1 = (...args) => {
        const app = createApp$1(...args);
        app.on('destroy', destroy);
        state.apps.push(app);
        return createAppAPI(app);
    };

    // destroys apps and removes them from the app array
    destroy = hook => {
        
        // returns true if the app was destroyed successfully
        const indexToRemove = state.apps.findIndex(app => app.isAttachedTo(hook));
        if (indexToRemove >= 0) {
            // remove from apps
            const app = state.apps.splice(indexToRemove, 1)[0];
            
            // restore original dom element
            app.restoreElement();

            return true;
        }

        return false;
    };

    // parses the given context for plugins (does not include the context element itself)
    parse = context => {
        // get all possible hooks
        const matchedHooks = Array.from(context.querySelectorAll(`.${name}`));

        // filter out already active hooks
        const newHooks = matchedHooks.filter(
            newHook => !state.apps.find(app => app.isAttachedTo(newHook))
        );

        // create new instance for each hook
        return newHooks.map(hook => create$1(hook));
    };

    // returns an app based on the given element hook
    find = hook => {
        const app = state.apps.find(app => app.isAttachedTo(hook));
        if (!app) {
            return null;
        }
        return createAppAPI(app);
    };

    getOptions$1 = () => {
        const opts = {};
        forin(getOptions(), (key, value) => {
            opts[key] = value[0];
        });
        return opts;
    };

    setOptions$1 = opts => {

        if (isObject(opts)) {
            // update existing plugins
            state.apps.forEach(app => {
                app.setOptions(opts);
            });

            // override defaults
            setOptions(opts);
        }

        // return new options
        return getOptions$1();
    };
}

export { supported, OptionTypes, create$1 as create, destroy, parse, find, getOptions$1 as getOptions, setOptions$1 as setOptions };
