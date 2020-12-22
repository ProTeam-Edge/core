/* eslint-disable */

import React from 'react';
import { createDoka, updateDoka, propsToElementAttributes } from '../utils/createDoka';

export default class DokaOverlay extends React.Component {

    constructor(props) {
        super(props);
        this._root = null;
        this._instance = null;
    }

    componentWillUnmount() {
        this.hide();
    }

    componentDidMount() {
        this.update();
    }

    componentDidUpdate() {
        this.update();
    }
    
    update() {
        const { enabled } = this.props;
        if (enabled || typeof enabled === 'undefined') {
            this.show();
        }
        else {
            this.hide();
        }
    }

    show() {

        // handle closing of the instance ourself, we need output date to update the preview image
        const options = Object.assign({}, this.props, {
            styleLayoutMode: 'preview',
            outputData: true,
            onclose: () => {
                this._instance.destroy();
                this._instance = null;
            }
        });

        // update existing instance
        if (this._instance) {
            return updateDoka(this._instance, options);
        };

        // no instance yet, create a new one
        this._instance = createDoka(this, this._root, options);
    }

    hide() {
        if (!this._instance) return;
        this._instance.close();
    }

    render() {
        const { children } = this.props;
        return <div {...propsToElementAttributes({ className: "DokaOverlay" }, this.props)}>
            { children }
            <div className="DokaContainer">
                <div ref={ ref => this._root = ref }></div>
            </div>
        </div>
    }

}