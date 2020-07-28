import React from 'react';
import { Doka } from './react-doka';

const toDegrees = radians => parseFloat(radians || 0) * (180 / Math.PI);
const toRadians = degrees => parseFloat(degrees || 0) * (Math.PI / 180);

class DemoInline extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            enabled: false,
            src: './assets/photo.jpeg',
            crop: {}
        };

        this.dokaRef = React.createRef();

        this.handleDokaConfirm = this.handleDokaConfirm.bind(this);
        this.handleDokaCancel = this.handleDokaCancel.bind(this);
        this.handleRotationChange = this.handleRotationChange.bind(this);
        this.handleZoomChange = this.handleZoomChange.bind(this);
        this.handleHorizontalCenterChange = this.handleHorizontalCenterChange.bind(this);
        this.handleVerticalCenterChange = this.handleVerticalCenterChange.bind(this);
    }

    handleDokaConfirm(output) {
        console.log('Confirmed Doka', output);
    }

    handleDokaCancel() {
        console.log('Cancelled Doka');

        console.log(this.dokaRef.current);
    }

    handleRotationChange(e) {
        this.setState({
            crop: {
                ...this.state.crop,
                rotation: toRadians(e.target.value)
            }
        })
    }

    handleZoomChange(e) {
        this.setState({
            crop: {
                ...this.state.crop,
                zoom: Math.max(1, e.target.value)
            }
        })
    }

    handleHorizontalCenterChange(e) {
        this.setState({
            crop: {
                ...this.state.crop,
                center: {
                    x: e.target.value / 100,
                    y: this.state.crop.center ? this.state.crop.center.y || .5 : .5
                }
            }
        })
    }

    handleVerticalCenterChange(e) {
        this.setState({
            crop: {
                ...this.state.crop,
                center: {
                    x: this.state.crop.center ? this.state.crop.center.x || .5 : .5,
                    y: e.target.value / 100
                }
            }
        })
    }

    render() {
        const { src, crop } = this.state;
        
        return <div>
        
            <h2>Doka Inline</h2>

            <p>
                <label>Rotation</label>
                <input type="number" step="15" value={ Math.round(toDegrees(this.state.crop.rotation)) } onChange={ this.handleRotationChange }/>
            </p>

            <p>
                <label>Zoom</label>
                <input type="number" step=".5" value={ Math.max(1, this.state.crop.zoom || 1) } onChange={ this.handleZoomChange }/>
            </p>

            <p>
                <label>Horizontal Center</label>
                <input type="number" step="5" value={ this.state.crop.center ? Math.round((this.state.crop.center.x || .5) * 100) : 50 } onChange={ this.handleHorizontalCenterChange }/>
            </p>

            <p>
                <label>Vertical Center</label>
                <input type="number" step="5" value={ this.state.crop.center ? Math.round((this.state.crop.center.y || .5) * 100) : 50 } onChange={ this.handleVerticalCenterChange }/>
            </p>

            <Doka
                style={{ width: '640px', height: '480px' }}
                utils={['crop', 'filter', 'color', 'markup', 'resize']}
                ref={ this.dokaRef }
                crop={ crop }
                src={ src }
                onConfirm={ this.handleDokaConfirm }
                onCancel={ this.handleDokaCancel }/>

        </div>
    }
}

export default DemoInline;