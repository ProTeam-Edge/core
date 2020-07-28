/* eslint-disable */

import React from 'react';
import { createDoka, updateDoka, propsToElementAttributes } from '../utils/createDoka';

const isInt = str => /^[0-9]+$/.test(str + '');

export default class Doka extends React.Component {

    constructor(props) {
        super(props);
        this._root = null;
        this._instance = null;
    }

    componentDidMount() {
        this._instance = createDoka(this, this._root, this.props);
    }

    componentDidUpdate() {
        updateDoka(this._instance, this.props);
    }

    componentWillUnmount() {
        if (!this._instance) return;
        this._instance.destroy();
    }

    render() {
        const { width, height, children } = this.props;
        const styleWidth = isInt(width) ? `${width}px` : width || null;
        const styleHeight = isInt(height) ? `${height}px` : height || null;

        return <div {...propsToElementAttributes({ className: "Doka", style: { width: styleWidth, height: styleHeight } }, this.props)}>
            <div ref={ ref => this._root = ref }>
                { children }
            </div>
        </div>
    }

}