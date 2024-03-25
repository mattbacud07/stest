$(document).ready(function () {

    // Select2 Settings
    $(document).on('select2:open', () => {
        document.querySelector('.select2-search__field').focus();
    });


    // Flatpickr Date
    // $('.flatpickr-date-delivery-date').flatpickr({
    //     minDate : 'today'
    // })
    // date picker 
    //   if($('#propose_delivery_date').length) {
    //     flatpickr("#propose_delivery_date", {
    //       wrap: true,
    //       minDate: 'today',
    //       dateFormat: "Y-m-d",
    //     });
    //   }
    $('#propose_delivery_date').flatpickr({
        minDate: 'today'
    })
    // Institution Select2
    $('#getInstitution').select2();

    $('#getInstitution').on('select2:select', function (e) {
        // Get the selected option
        var selectedOption = $(e.params.data.element);

        // Get the value of data-InstitutionAddress attribute
        var institutionAddress = selectedOption.data('institutionaddress');

        // Do something with the institutionAddress value
        $('#address').val(institutionAddress);
    })

    $('#dataTableEquipment').DataTable()

    $('#dataTableEquipment tbody').on('click', 'tr', function () {
        // $(this).toggleClass('selected');

        var row = $(this);

        var id = row.find('td:eq(0)').text();
        var itemCode = row.find('td:eq(1)').text();
        var description = row.find('td:eq(2)').text();

        // if(row.hasClass('selected')){
        // equipment = $('#equipment')
        // additional = $('#additionalEquipment')
        // equipment.hasClass('active') ?  tableToInsert = $('#equipmentPeripherals') : ''
        // additional.hasClass('active') ?  tableToInsert = $('#additionalEquipmentPeripherals') : ''

        tableToInsert = $('#equipmentPeripherals')
        equipmentAppend = `
                <tr>
                    <td><input type='text' value='${itemCode}' name='equipment_description[]' required readonly></td>
                    <td><input type='text' value='${description}' name='item_code[]' required readonly></td>
                    <td><input type='text' name='equipment_serial[]' required></td>
                    <td><input type='text' name='equipment_remarks[]'></td>
                    <td><input type='hidden' value='${id}' name='equipment_id[]'><button type='button' id='remove_row' class='btn btn-xs btn-outline-danger'>Remove</button></td>
                </tr>
                `

        tableToInsert.append(equipmentAppend);

        // }else {
        //     // Remove the corresponding row from the equipmentPeripherals table
        //     $('#equipmentPeripherals tr').each(function () {
        //         if ($(this).find('td:eq(0) input').val() === description) {
        //             $(this).remove();
        //         }
        //     });
        // }


    });


    /**
     * Additional Equipment Peripherals
     */
    $('#dataTableAdditionalEquipment').DataTable()
    $('#dataTableAdditionalEquipment tbody').on('click', 'tr', function () {
        // $(this).toggleClass('selected');

        var row = $(this);

        var id = row.find('td:eq(0)').text();
        var itemCode = row.find('td:eq(1)').text();
        var description = row.find('td:eq(2)').text();

        tableToInsert = $('#additionalEquipmentPeripherals')
        equipmentAppend = `
        <tr>
            <td><input type='text' value='${itemCode}' name='add_equipment_description[]' required readonly></td>
            <td><input type='text' value='${description}' name='add_item_code[]' required readonly></td>
            <td><input type='text' name='add_equipment_serial[]' required></td>
            <td><input type='text' name='add_equipment_remarks[]'></td>
            <td><input type='hidden' value='${id}' name='add_equipment_id[]'><button type='button' id='remove_row_additional' class='btn btn-xs btn-outline-danger'>Remove</button></td>
        </tr>
        `

        tableToInsert.append(equipmentAppend);
    });


    /**
     * Remove Selected Item in Master Data
     */
    $(document).on('click', '#remove_row', function () {
        tr = $(this).closest('tr');
        tr.remove();
    })

    /**
     * Remove Selected Item in Master Data Additional Equipment
     */
    $(document).on('click', '#remove_row_additional', function () {
        tr = $(this).closest('tr');
        tr.remove();
    })

    /**
     * Choosing of Request type (Internal or Extenal Request)
     */
    $(document).on('change', 'input[name="request_type"]', function () {
        if ($('#internalRequest').is(':checked')) {
            $('#display_internal_request').removeClass('d-none')
            $('#display_external_request').addClass('d-none')
        } else {
            $('#display_internal_request').addClass('d-none')
            $('#display_external_request').removeClass('d-none')
        }
    })
})