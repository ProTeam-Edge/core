function useDokaWithUppy(Doka, options) {

    var doka = Doka.create(options);

    var queue = [];

    function editNextFile() {
        var next = queue[0];
        if (next) {
            next();
        }
    }

    function queueFile(file) {
        
        // Queue for editing
        queue.push(function() {
            doka.edit(file.data).then(function(output) {
    
                // Remove this item from the queue
                queue.shift();
    
                // Edit next item in queue
                editNextFile();
    
                // Don't add file if cancelled
                if (!output) return;
    
                // add the modified file
                uppy.addFile({
                    ...file,
                    data: output.file,
                    handledByDoka: true
                });
            });
        });

        // If this is first item, let's open the editor immmidiately
        if (queue.length === 1) {
            editNextFile();
        }
    }

    return function(file) {

        if (file.handledByDoka) return true;

        // edit first, then add manually
        queueFile(file);

        // can't add now, we have to wait for editing to finish
        return false;
    };
}