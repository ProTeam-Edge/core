<script>
import { Doka } from './svelte-doka';

let cropAspectRatio = 1;

const handleConfirm = (output) => {
    console.log('Confirm edit!', output);
}

const handleCancel = () => {
    console.log('Cancel edit!');
}
</script>

<div>

    <h2>Demo Inline</h2>

    <Doka 
        style="width: 800px; height: 480px"
        src={'./assets/photo.jpeg'}
        utils='crop, filter, color, markup, resize'
        {cropAspectRatio}
        oncancel={handleCancel}
        onconfirm={handleConfirm}
        cropAspectRatioOptions={[
            {
                label: 'Free',
                value: null
            },
            {
                label: 'Portrait',
                value: 1.5
            },
            {
                label: 'Square',
                value: 1
            },
            {
                label: 'Landscape',
                value: .75
            }
        ]}/>
    
</div>