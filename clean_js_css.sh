#!/bin/bash

echo "Removing all comments from JavaScript and CSS files..."

# Find and process JavaScript files
find . -name "*.js" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./public/build/*" | while read file; do
    echo "Processing JS: $file"
    # Remove single-line comments
    sed -i 's|//.*$||g' "$file"
    # Remove multi-line comments (simple approach)
    sed -i ':a;N;$!ba;s|/\*[^*]*\*\+\([^/*][^*]*\*\+\)*/||g' "$file"
    # Clean empty lines
    sed -i '/^\s*$/d' "$file"
done

# Find and process CSS files
find . -name "*.css" -not -path "./vendor/*" -not -path "./node_modules/*" -not -path "./public/build/*" | while read file; do
    echo "Processing CSS: $file"
    # Remove CSS comments
    sed -i ':a;N;$!ba;s|/\*[^*]*\*\+\([^/*][^*]*\*\+\)*/||g' "$file"
    # Clean empty lines
    sed -i '/^\s*$/d' "$file"
done

echo "Finished removing comments from JS and CSS files!"
