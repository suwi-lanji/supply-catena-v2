<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DnD Invoice Template Maker - {{ ucfirst($template_name) }}</title>
  <link rel="stylesheet" href="https://code.jquery.com/ui/1.14.0/themes/base/jquery-ui.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
      margin: 0;
      background-color: #f4f4f4;
    }
    #sidePanel {
      width: 250px;
      background-color: #ffffff;
      padding: 10px;
      border-right: 2px solid #007bff;
      box-sizing: border-box;
      overflow-y: auto;
    }
    #mainEditPage {
      flex: 1;
      padding: 20px;
      background-color: #ffffff;
      border-left: 2px solid #007bff;
      position: relative;
      overflow: hidden;
      box-sizing: border-box;
      height: 100%;
    }
    .component {
      margin-bottom: 10px;
      padding: 10px;
      background-color: #007bff;
      color: white;
      text-align: center;
      cursor: move;
      z-index: 1000;
      border-radius: 4px;
    }

    .droppable {
      border: 2px dashed #007bff;
      height: calc(100vh - 30px);
      width: 100%;
      position: relative;
      box-sizing: border-box;
    }
    .draggable-item {
      position: absolute;
      padding: 2px;
      z-index: 1000;
      box-sizing: border-box;
    }
    .remove-button {
      background: #dc3545;
      color: white;
      border: none;
      padding: 5px;
      cursor: pointer;
      border-radius: 4px;
      font-size: 12px;
      position: absolute;
      top: 5px;
      right: 5px;
    }
    .remove-button:hover {
      background: #c82333;
    }
    #textStylePanel {
      margin-top: 20px;
    }
    .text-style-controls {
      display: flex;
      flex-direction: column;
    }
    .text-style-controls label {
      margin-bottom: 5px;
      font-weight: bold;
    }
    .text-style-controls select, .text-style-controls input {
      margin-bottom: 10px;
      padding: 8px;
      border-radius: 4px;
      border: 1px solid #ccc;
      font-size: 14px;
    }
    .selected {
      border: 2px dashed #007bff;
    }
    .table-centered td, .table-centered th {
      vertical-align: middle !important;
    }
    .table-borderless>:not(caption)>*>* {
      border-bottom-width: 0;
    }
    tbody, td, tfoot, th, thead, tr {
      border-color: inherit;
      border-style: solid;
      border-width: 0;
    }
    table {
      width: 100%;
      height: 100%;
    }
    .table {
      width: 100%;
      margin-bottom: 1.5rem;
      color: var(--ct-table-color);
      vertical-align: top;
      border-color: var(--ct-table-border-color);
    }
    th, td {
      border: 1px solid #ccc;
      padding: 3px;
      text-align: left;
    }
    input[type="text"] {
      padding: 8px;
      font-size: 14px;
      border: 1px solid #ccc;
      border-radius: 4px;
      margin-bottom: 10px;
      box-sizing: border-box;
    }
    button {
      padding: 10px 20px;
      font-size: 16px;
      border: none;
      border-radius: 5px;
      background-color: #007bff;
      color: #fff;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    button:hover {
      background-color: #0056b3;
    }
    button:focus {
      box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.5);
      outline: none;
    }
    #tableControls {
      margin-top: 20px;
    }
    #tableControls button {
      margin-top: 10px;
    }
    #tableTitle {
      width: calc(100% - 18px); /* Adjust width to fit within the side panel */
      padding: 8px;
      border: 1px solid #ccc;
      border-radius: 4px;
      box-sizing: border-box;
    }
    #customTextConfig {
      display: flex;
      flex-direction: column;
      margin-top: 20px;
    }
    #customTextConfig input[type="text"] {
      width: calc(100% - 18px); /* Adjust width to fit within the side panel */
    }
  </style>
  <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
  <script src="https://code.jquery.com/ui/1.14.0/jquery-ui.js"></script>
  <script>
$(function() {
  var $selectedElement = null;

  // Draggable and Droppable initialization
  $(".component").draggable({
      helper: "clone",
      zIndex: 1000
  });

  $(".droppable").droppable({
    accept: ".component",
    drop: function(event, ui) {
        var droppedComponent = $(ui.helper).clone();
        droppedComponent.removeClass("component").addClass("draggable-item");

        var elementType = ui.helper.data('type');
        var defaultValue = ui.helper.data('value');
        var elementName = ui.helper.data('name');
        
        // Handle different element types
        if (elementType === "custom-table") {
            droppedComponent.html('<div class="custom-table"><span>Table Title</span><table><thead></thead><tbody></tbody></table></div>');
        } else if (elementType === "image") {
            droppedComponent.html('<img src=' + defaultValue + ' />');
        } else if (elementType === "text_field") {
            const htmlText = JSON.parse(defaultValue).toString().replace(/\\n/g, "<br/>");
            droppedComponent.html('<p>' + htmlText + '</p>');
        } else if (elementType === "key_value") {
            let formattedText = elementName
                .split('_')
                .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
                .join(' ');
            droppedComponent.html('<p><strong style="margin-right: 0.2rem">' + formattedText + ':</strong>' + JSON.parse(defaultValue) + '</p>');
        } else if (elementType === 'custom_text') {
            droppedComponent.html("<p contenteditable='true' class='customText'>Click to change text</p>");
        } else if (elementType === 'date') {
            droppedComponent.html('<p>' + formatDate(JSON.parse(defaultValue)) + '</p>');
        } else if (elementType === 'table') {
            let tableData = {};

            try {
                const parsedOnce = JSON.parse(defaultValue);
                tableData = typeof parsedOnce === 'string' ? JSON.parse(parsedOnce) : parsedOnce;
            } catch (e) {
                console.error("Error parsing table data:", e);
            }

            const tableColumns = tableData.headers || [];
            const tableRows = tableData.rows || [];

            if (tableColumns.length > 0) {
                const htmlTable = `
                    <table>
                        <thead>
                            <tr>
                                ${tableColumns.map(column => `<th colspan="${column.colspan || 1}">${column.text.replace(/\\n/g, '<br>')}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${tableRows.map(row => `
                                <tr>
                                    ${Object.values(row).map(value => `<td>${value}</td>`).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>`;
                droppedComponent.html(htmlTable);
            } else {
                console.warn("Table data is not defined or empty.");
                droppedComponent.html('<p>Error: Table data not defined</p>');
            }
        }

        // Position the component within the container
        var containerOffset = $(this).offset();
        droppedComponent.css({
            "top": ((ui.position.top - containerOffset.top) / $(this).height()) * 100 + "%",
            "left": ((ui.position.left - containerOffset.left) / $(this).width()) * 100 + "%"
        });

        // Add a remove button
        var removeButton = $('<button class="remove-button">X</button>');
        droppedComponent.append(removeButton);
        
        // Append the dropped component to the container
        $(this).append(droppedComponent);
        
        // Make the new component draggable and resizable
        makeDraggableAndResizable(droppedComponent);
        
        // Set the newly added element as selected
        selectElement(droppedComponent);
    }
});

  function formatDate(dateString) {
    // Create a Date object from the dateString
    const date = new Date(dateString);

    // Format the date to a human-readable form
    return date.toLocaleDateString('en-US', {
        weekday: 'long', // e.g., "Monday"
        year: 'numeric', // e.g., "2024"
        month: 'long',   // e.g., "August"
        day: 'numeric'   // e.g., "30"
    });
}
  function makeDraggableAndResizable(element) {
      element.draggable({
          containment: "#mainEditPage",
          zIndex: 1000
      }).resizable({
          containment: "#mainEditPage"
      });

      element.click(function() {
          selectElement($(this));
      });

      element.find(".remove-button").click(function() {
          $(this).parent().remove();
          if ($selectedElement && $selectedElement.is($(this).parent())) {
              $selectedElement = null;
              resetStyleControls();
          }
      });
  }

  function selectElement(element) {
    console.log(element.find('p'))
      if ($selectedElement) {
          $selectedElement.removeClass("selected");
      }
      $selectedElement = element;
      $selectedElement.addClass("selected");
      $("[contenteditable]",this).focus()
      updateStyleControls();
  }

  function updateStyleControls() {
      if ($selectedElement) {
          var p = $selectedElement.find('p');
          $('#fontSize').val(parseFloat(p.css('font-size')));
          $('#fontColor').val(p.css('color'));
          $('#fontFamily').val(p.css('font-family'));
          $('#fontWeight').val(p.css('font-weight'));
          $('#fontStyle').val(p.css('font-style'));
          $('#textDecoration').val(p.css('text-decoration'));
      }
  }

  function resetStyleControls() {
      $('#fontSize').val('');
      $('#fontColor').val('#000000');
      $('#fontFamily').val('Arial');
      $('#fontWeight').val('normal');
      $('#fontStyle').val('normal');
      $('#textDecoration').val('none');
  }

  $('#fontSize').change(function() {
      if ($selectedElement) {
          var fontSize = $(this).val() + 'px';
          $selectedElement.find('p').css('font-size', fontSize);
      }
  });

  $('#fontColor').change(function() {
      if ($selectedElement) {
          var fontColor = $(this).val();
          $selectedElement.find('p').css('color', fontColor);
      }
  });

  $('#fontFamily').change(function() {
      if ($selectedElement) {
          var fontFamily = $(this).val();
          $selectedElement.find('p').css('font-family', fontFamily);
      }
  });

  $('#fontWeight').change(function() {
      if ($selectedElement) {
          var fontWeight = $(this).val();
          $selectedElement.find('p').css('font-weight', fontWeight);
      }
  });

  $('#fontStyle').change(function() {
      if ($selectedElement) {
          var fontStyle = $(this).val();
          $selectedElement.find('p').css('font-style', fontStyle);
      }
  });

  $('#textDecoration').change(function() {
      if ($selectedElement) {
          var textDecoration = $(this).val();
          $selectedElement.find('p').css('text-decoration', textDecoration);
      }
  });

  $('#cText').change(function () {
    if($selectedElement) {
      var text = $(this).val();
      $selectedElement.find('p').text(text)
    }
  })

  $('#exportTemplate').click(function() {
    // Clone the content of #droppable along with inline styles
    var clonedContent = $('#droppable').clone();
    
    // Remove elements with class 'remove-button' and 'ui-resizable-handle' from the cloned content
    clonedContent.find('.remove-button').remove();
    clonedContent.find('.ui-resizable-handle').remove();
    clonedContent.find('.draggable-item').each(function() {
        var $this = $(this);
        
        // Get pixel positions
        var topPx = parseFloat($this.css('top'));
        var leftPx = parseFloat($this.css('left'));
        
        // Get the container's dimensions
        var containerWidth = $('#droppable').width();
        var containerHeight = $('#droppable').height();
        
        // Convert pixel positions to percentages
        var topPercent = (topPx / containerHeight) * 100;
        var leftPercent = (leftPx / containerWidth) * 100;
        
        // Update the position in percentage
        $this.css({
            top: topPercent + '%',
            left: leftPercent + '%'
        });
    });
    // Iterate over each element with data-type attribute
    clonedContent.find('[data-type]').each(function() {
        var $this = $(this);
        var dataType = $this.data('type');
        var dataName = $this.data('name');
        
        // Check if data-type is 'date' or 'text_field'
        if (dataType === 'date' || dataType === 'text_field') {
            // Use a custom placeholder syntax
            $this.find('p').first().text(`__FIELD_${dataName}__`);
        } else if (dataType === 'table') {
            // Replace table content with placeholders
            var $table = $this.find('table');
            var columnsPlaceholder = `__FIELD_${dataName}_COLUMNS__`;
            var rowsPlaceholder = `__FIELD_${dataName}_ROWS__`;
            $table.find('thead').html(`<tr><th>${columnsPlaceholder}</th></tr>`);
            $table.find('tbody').html(`<tr><td>${rowsPlaceholder}</td></tr>`);
        }
    });

    // Optional: Extract styles and append them to the cloned content
    var styles = '<head><meta name="viewport" content="width=device-width, initial-scale=1"/><style>body{position: relative; width: 100%;height: 100%}#tableTitle,input[type=text]{padding:8px;box-sizing:border-box}#tableTitle,.draggable-item,.droppable,input[type=text]{box-sizing:border-box}#customTextConfig input[type=text],#tableTitle{width:calc(100% - 18px)}.droppable{height:calc(100vh - 30px);width:100%;position:relative}.draggable-item{position:absolute;padding:2px;z-index:1000}.table-centered td,.table-centered th{vertical-align:middle!important}.table-borderless>:not(caption)>*>*{border-bottom-width:0}tbody,td,tfoot,th,thead,tr{border:0 solid;border-color:inherit}table{width:100%;height:100%}.table{width:100%;margin-bottom:1.5rem;color:var(--ct-table-color);vertical-align:top;border-color:var(--ct-table-border-color)}td,th{border:1px solid #ccc;padding:3px;text-align:left}input[type=text]{font-size:14px;border:1px solid #ccc;border-radius:4px;margin-bottom:10px}button{padding:10px 20px;font-size:16px;border:none;border-radius:5px;background-color:#007bff;color:#fff;cursor:pointer;transition:background-color .3s}button:hover{background-color:#0056b3}button:focus{box-shadow:0 0 0 2px rgba(0,123,255,.5);outline:0}#tableControls{margin-top:20px}#tableControls button{margin-top:10px}#tableTitle{border:1px solid #ccc;border-radius:4px}#customTextConfig{display:flex;flex-direction:column;margin-top:20px}</style></head>';

// Prepare the HTML content with styles for export
    var templateContent = "<html>" + styles + '<body> <div style="position: relative;width: 100vw;height: 100vh;overflow: hidden;">' + clonedContent.html() + '</div></body>' + "</html>";

    // Send the HTML content to the server
    $.ajax({
        url: '/save-template', // URL to your controller method
        method: 'POST',
        data: {
            _token: `{{ csrf_token() }}`, // CSRF token
            tenant_id: `{{ $tenant_id }}`,
            template_name: $('#templateName').val(),
            template: templateContent
        },
        success: function(response) {
            alert('Template saved successfully!');
        },
        error: function(xhr) {
            console.error('Failed to save template:', xhr.responseText);
        }
    });
});

  $('#tableTitle').change(function() {
      if ($selectedElement && $selectedElement.find('.custom-table span').length) {
          $selectedElement.find('.custom-table').find('span').text($(this).val());
      }
  });

  $('#addTableHeader').click(function() {
      if ($selectedElement && $selectedElement.find('.custom-table table').length) {
          var $table = $selectedElement.find('.custom-table table');
          $table.find('thead').append('<tr><</tr>');
      }
  });

  $('#addTableRow').click(function() {
      if ($selectedElement && $selectedElement.find('.custom-table table').length) {
          var $table = $selectedElement.find('.custom-table table');
          
          // Determine the number of columns from the header
          var columnCount = $table.find('thead th').length;
          
          // Create a new row with the same number of columns
          var newRow = '<tr>';
          for (var i = 0; i < columnCount; i++) {
              newRow += '<td>Row Content</td>';
          }
          newRow += '</tr>';
          
          // Append the new row to the table body
          $table.find('tbody').append(newRow);
      }
  });

  

  $('#addTableColumn').click(function() {
      if ($selectedElement && $selectedElement.find('.custom-table table').length) {
          var $table = $selectedElement.find('.custom-table table');
          
          if(!$table.find('thead tr').length) {
          $table.find('thead tr').append('<th contenteditable="true">Header</th>');
          $table.find('thead').append('<tr></tr>');
          }

          $table.find('thead tr').append('<th>Column</th>');
          
          // Add a new column to each row in the tbody
          $table.find('tbody tr').each(function() {
              $(this).append('<td contenteditable="true">Column Content</td>');
          });
      }
  });
});



</script>

</head>
<body style="position: fixed; width: 100%; height: 100%; display: flex; flex-direction: column;">


<div style="width: 100%; height: 92%; display: flex; flex-direction: row; flex-grow: 1; padding-bottom: 10px">
<div id="sidePanel" style="overflow-y: scroll">
  @foreach ($template_fields as $field)
  <div class="component" data-type="{{ $field['type'] }}" data-name="{{ $field['name'] }}" data-value="{{ json_encode($field['default']) }}">
    {{ ucfirst(str_replace('_', ' ', $field['name'])) }}
</div>

  @endforeach
  <div class="component" data-type="custom-table" data-value="">
  Add Custom Table
</div>
<div class="component" data-type="custom_text" data-value="">
  Add Custom Text
</div>
<div id="tableControls">
  <button id="addTableRow">Add Row</button>
  <div class="">
    <p style="font-weight: bold; text-size: 1rem; text-align: center;">Add Column</p>
    <label for="columnType">Set Column Type: </label>
    <select id="columnType" value="custom">
      <option value="custom">Custom</option>
      <option value="model">Model</option>
    </select>

    <label for="columnModel">Set Column Source: </label>
    <select name="columnSource" id="columnSource">
      <option value="hello">Hello</option>
      <option value="word">World</option>
    </select>
    <button id="addTableColumn">Add Column</button>
  </div>
  <input type="text" id="tableTitle" placeholder="Table Title" style="margin-top: 10px; padding: 5px; border-radius: 10px; border: 1px black solid;"/>
</div>

<div id="customTextConfig" style="display: flex; flex-direction: row;padding-top: 10px">
  <input id="cText" type="text" style="padding: 5px; border-radius: 10px; border: 1px black solid;" placeholder="Add Custom Text"/>
</div>

  <!-- Add Text Styling Controls -->
  <div id="textStylePanel">
    <div class="text-style-controls">
      <label for="fontSize">Font Size (px):</label>
      <input type="number" id="fontSize" step="1" min="1" />

      <label for="fontColor">Font Color:</label>
      <input type="color" id="fontColor" value="#000000"/>

      <label for="fontFamily">Font Family:</label>
      <select id="fontFamily">
        <option value="Arial">Arial</option>
        <option value="Courier New">Courier New</option>
        <option value="Georgia">Georgia</option>
        <option value="Times New Roman">Times New Roman</option>
        <option value="Verdana">Verdana</option>
      </select>

      <label for="fontWeight">Font Weight:</label>
      <select id="fontWeight">
        <option value="normal">Normal</option>
        <option value="bold">Bold</option>
        <option value="bolder">Bolder</option>
        <option value="lighter">Lighter</option>
      </select>

      <label for="fontStyle">Font Style:</label>
      <select id="fontStyle">
        <option value="normal">Normal</option>
        <option value="italic">Italic</option>
        <option value="oblique">Oblique</option>
      </select>

      <label for="textDecoration">Text Decoration:</label>
      <select id="textDecoration">
        <option value="none">None</option>
        <option value="underline">Underline</option>
        <option value="overline">Overline</option>
        <option value="line-through">Line-through</option>
      </select>
    </div>
  </div>
</div>

<div id="mainEditPage">
  <div class="droppable" id="droppable">
    <!-- Draggable components will be dropped here -->
    @if ($template_data)
      @foreach ($template_fields as $field)
        <div class="draggable-item" style="position: relative; top: 0; left: 0;">
          @if ($field['type'] === 'text_field' || $field['type'] === 'date')
            <input type="text" value="{{ $template_data->{$field['name']} ?? '' }}" style="width: 100%;" />
          @elseif ($field['type'] === 'relationship')
            <select style="width: 100%;">
              @foreach (DB::table($field['table'])->get() as $option)
                <option value="{{ $option->id }}" {{ $template_data->{$field['name']} == $option->id ? 'selected' : '' }}>
                  {{ $option->name }}
                </option>
              @endforeach
            </select>
          @elseif ($field['type'] === 'image')
            <img src="{{ $template_data->{$field['name']} }}" style="max-width: 100%;"/>
          @endif
          <button class="remove-button">X</button>
        </div>
      @endforeach
    @endif
  </div>
</div>

</div>
<div style="margin: 10px 0; text-align: center;width:100%;height:100%">
    <!-- Input box for template name -->
    <input 
        type="text" 
        id="templateName" 
        placeholder="Enter template name" 
        style="
            padding: 8px 12px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 10px;
            width: 100%;
            max-width: 300px;
            box-sizing: border-box;
        "
    />

    <!-- Button to export the template -->
    <button 
        id="exportTemplate" 
        style="
            padding: 10px 20px;
            font-size: 16px;
            border: none;
            border-radius: 5px;
            background-color: #007bff;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        "
        onmouseover="this.style.backgroundColor='#0056b3';"
        onmouseout="this.style.backgroundColor='#007bff';"
        onfocus="this.style.boxShadow='0 0 0 2px rgba(0, 123, 255, 0.5)';"
        onblur="this.style.boxShadow='none';"
    >
        Export Template
    </button>
</div>
</body>
</html>
