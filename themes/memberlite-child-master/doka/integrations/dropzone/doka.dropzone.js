function useDokaWithDropzone(Doka, options) {

    // Create a default Doka editor
    var doka = Doka.create(options);

    // File queue to edit
    var queue = [];

    function editNextFile() {
        var next = queue[0];
        if (next) {
            next();
        }
    }

    function queueFile(dz, file, done) {

        // Queue for editing
        queue.push(function() {
            doka.edit(file).then(function(output) {

                // Remove this item from the queue
                queue.shift();

                // Edit next item in queue
                editNextFile();

                // No output means cancelled, remove file from list
                if (!output) {
                    dz.removeFile(file);
                    done();
                    return;
                };
                
                // Create new thumbnail
                dz.createThumbnail(
                    output.file,
                    dz.options.thumbnailWidth,
                    dz.options.thumbnailHeight,
                    dz.options.thumbnailMethod,
                    false, 
                    function(dataURL) {
                        
                        // Update the thumbnail
                        dz.emit('thumbnail', file, dataURL);
    
                        // Return modified file to dropzone
                        done(output.file);
                    });
                
            });
        });

        // If this is first item, let's open the editor immmidiately
        if (queue.length === 1) {
            editNextFile();
        }
    }

    // expose transformFile method
    return function(file, done) {
        queueFile(this, file, done);
    }
};