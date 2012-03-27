#!/bin/sh

echo "Compiling"

echo "// ==ClosureCompiler==" > ./../all.js
echo "// @compilation_level SIMPLE_OPTIMIZATIONS" >> ./../all.js
echo "// @output_file_name all.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/lib-structures.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/api.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/common.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/error-handler.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/ui-controls.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/ui-boxes.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/content.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/menu.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/error.js" >> ./../all.js
echo "// @code_url http://pipeinpipe.info/js/main.js" >> ./../all.js
echo "// @formatting print_input_delimiter" >> ./../all.js
echo "// ==/ClosureCompiler==" >> ./../all.js
echo "" >> ./../all.js

java -jar compiler.jar --formatting PRINT_INPUT_DELIMITER --compilation_level SIMPLE_OPTIMIZATIONS ./../lib-structures.js ./../api.js ./../common.js ./../error-handler.js ./../ui-controls.js ./../ui-boxes.js ./../content.js ./../menu.js ./../error.js ./../main.js >> ./../all.js

echo "."