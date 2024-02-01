# RaspberryPI api gpio control
>This project requires PHP8, SQLite3 and a php compatible httpd(apache2, ngix, LightHTTPd)
+ ## api.php
    ### Contain a POST JSON based api
    > only accept post method
     * Requires an JSON POST with a command, otherwise return an error in json format
     * The json command divides in three entries (go to ExPost.json for an example request)
         - 'api_key': contain a key to validate the user (example keys included in ValidTestApiKeys.txt)
         - 'command': contain a command to obtain diferent responses.
          + 'flip': flip the current I/O status of the designated gpio pin and return his status
          + 'status': return the current I/O status of the designated gpio pin
          + 'allStatus': return the current I/O status of all the pins
         - 'pin': the number of the gpio pin to designate
     * Return a json in the following format
         - 'Status': http response status code number
         - 'Title': http response status code title
         - 'Detail': a small description of the return/error
         - 'Data Response': only used on response with multiple data like the array of status of the command 'allStatus'
+ ## index.php
    ### To be implemented ui
+ ## apikeys.db (sqlite database)
    ### Contains the allowed user and keys (recreate before use)
+ ## .config Folder
    ### Contain the configuration files and services