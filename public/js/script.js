$(function() {
    var $selectedElement = null;

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

            switch (elementType) {
                case "text_field":
                case "date":
                    droppedComponent.html(`<input type="${elementType === 'text_field' ? 'text' : 'date'}" value="${defaultValue}" style="width: 100%;" />`);
                    break;
                case "image":
                    droppedComponent.html(`<img src="${defaultValue}" alt="Image" style="width: 100%;"/>`);
                    break;
                case "custom_table":
                    droppedComponent.html(`
                        <table class="custom-table" border="1">
                            <caption contenteditable="true">Table Title</caption>
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    `);
                    break;
                default:
                    droppedComponent.html(elementType);
                    break;
            }

            droppedComponent.css({
                "top": ui.position.top - $(this).offset().top,
                "left": ui.position.left - $(this).offset().left
            });

            var removeButton = $('<button class="remove-button">X</button>');
            droppedComponent.append(removeButton);
            $(this).append(droppedComponent);
            makeDraggableAndResizable(droppedComponent);

            // Set the newly added element as selected
            selectElement(droppedComponent);
        }
    });

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
        if ($selectedElement) {
            $selectedElement.removeClass("selected");
        }
        $selectedElement = element;
        $selectedElement.addClass("selected");
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

    $('#addTableHeader').click(function() {
        if ($selectedElement && $selectedElement.find('table').length) {
            var table = $selectedElement.find('table');
            var headerRow = $('<tr></tr>');
            var columnCount = table.find('thead tr th').length || 1;
            for (var i = 0; i < columnCount; i++) {
                headerRow.append('<th contenteditable="true">Header</th>');
            }
            table.find('thead').append(headerRow);
        }
    });

    $('#addTableRow').click(function() {
        if ($selectedElement && $selectedElement.find('table').length) {
            var table = $selectedElement.find('table');
            var row = $('<tr></tr>');
            var columnCount = table.find('thead tr th').length || 1;
            for (var i = 0; i < columnCount; i++) {
                row.append('<td contenteditable="true">Cell</td>');
            }
            table.find('tbody').append(row);
        }
    });

    $('#addTableColumn').click(function() {
        if ($selectedElement && $selectedElement.find('table').length) {
            var table = $selectedElement.find('table');
            table.find('thead tr').append('<th contenteditable="true">Header</th>');
            table.find('tbody tr').each(function() {
                $(this).append('<td contenteditable="true">Cell</td>');
            });
        }
    });

    $('#tableTitle').change(function() {
        if ($selectedElement && $selectedElement.find('table').length) {
            var table = $selectedElement.find('table');
            table.find('caption').text($(this).val());
        }
    });
});
