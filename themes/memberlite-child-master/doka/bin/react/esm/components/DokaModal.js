/* eslint-disable */

import React from 'react';
import { createDoka, updateDoka } from '../utils/createDoka';

export default class DokaModal extends React.Component  {

    constructor(props) {
        super(props);
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

        // handle closing of the instance ourself
        const options = Object.assign({}, this.props, {
            allowAutoClose: false,
            onclose: () => {
                this._instance.destroy();
                this._instance = null;
            }
        });

        // update existing instance
        if (this._instance) {
            return updateDoka(this._instance, options);
        };
        
        // no instance yet, create a new one (no root supplied, it's added to the <body>)
        this._instance = createDoka(this, null, options);
    }

    hide() {
        if (!this._instance) return;
        this._instance.close();
    }

    render() { 
        return null;
    }

}
