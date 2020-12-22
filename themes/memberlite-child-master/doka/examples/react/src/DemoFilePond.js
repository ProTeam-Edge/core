import React from 'react';
import { create } from './react-doka/lib/doka.esm.min';
import './react-doka/lib/doka.min.css';

import './DemoFilePond.css';
import { FilePond, registerPlugin } from 'react-filepond';

import 'filepond/dist/filepond.min.css';
import 'filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css';
import 'filepond-plugin-image-edit/dist/filepond-plugin-image-edit.min.css';

import FilePondPluginImageExifOrientation from 'filepond-plugin-image-exif-orientation';
import FilePondPluginImagePreview from 'filepond-plugin-image-preview';
import FilePondPluginImageEdit from 'filepond-plugin-image-edit';
import FilePondPluginImageCrop from 'filepond-plugin-image-crop';
import FilePondPluginImageResize from 'filepond-plugin-image-resize';
import FilePondPluginImageFilter from 'filepond-plugin-image-filter';
import FilePondPluginImageTransform from 'filepond-plugin-image-transform';


// Register the plugins
registerPlugin(
    FilePondPluginImageExifOrientation,
    FilePondPluginImageCrop,
    FilePondPluginImageResize,
    FilePondPluginImageFilter,
    FilePondPluginImagePreview,
    FilePondPluginImageEdit,
    FilePondPluginImageTransform
);

class DemoFilePond extends React.Component {

    constructor(props) {
        super(props);

        this.state = {
            files: []
        };

    }
    
    render() {
        return (
            <div>
                <h2>Doka React FilePond</h2>

                <FilePond
                    ref={ref => (this.pond = ref)}
                    files={this.state.files}
                    allowMultiple={true}
                    styleItemPanelAspectRatio={.5625}
                    imageCropAspectRatio="1:1"
                    imageFilterColorMatrix={[
                        0.150, 1.300,-0.250, 0.100,-0.200,
                        0.150, 1.300,-0.250, 0.100,-0.200,
                        0.150, 1.300,-0.250, 0.100,-0.200,
                        0.000, 0.000, 0.000, 1.000, 0.000
                    ]}
                    imageEditEditor={create({
                        cropAspectRatioOptions: [
                            {
                                label: 'Free',
                                value: null
                            },
                            {
                                label: 'Portrait',
                                value: 1.25
                            },
                            {
                                label: 'Square',
                                value: 1
                            },
                            {
                                label: 'Landscape',
                                value: .75
                            }
                        ]
                    })}
                    onupdatefiles={fileItems => {
                        this.setState({
                            files: fileItems.map(fileItem => fileItem.file)
                        });
                    }}
                />
            </div>
        );
      }
}

export default DemoFilePond;