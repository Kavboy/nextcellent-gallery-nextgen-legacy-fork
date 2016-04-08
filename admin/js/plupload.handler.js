/**
 * NextCellent handlers for plupload.
 *
 * These function connect the pluploader to our backend.
 *
 * @version 2.0.0 A large part of this code has moved to inline JavaScript.
 */
function debug() {

    'use strict';

    if (uploader.settings.debug) {
        plupload.each(arguments, function (message) {
            var exceptionMessage, exceptionValues = [];

            // Check for an exception object and print it nicely
            if (typeof message === "object" && typeof message.name === "string" && typeof message.message === "string") {
                for (var key in message) {
                    if (message.hasOwnProperty(key)) {
                        exceptionValues.push(key + ": " + message[key]);
                    }
                }
                exceptionMessage = exceptionValues.join("\n") || "";
                exceptionValues = exceptionMessage.split("\n");
                exceptionMessage = "EXCEPTION: " + exceptionValues.join("\nEXCEPTION: ");
                console.log(exceptionMessage);
            } else {
                console.log(message);
            }
        });
    }
}