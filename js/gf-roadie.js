jQuery(document).ready(function($){

    $('#button_address_add').on( 'click', e => {

    })


    $('#button_address_estimate').on( 'click', e => {
        e.preventDefault()

        let data = {
            shiping_query_quantity     : $('#shiping_query_quantity').val(),
            shiping_query_pickup_after : $('#shiping_query_pickup_after').val(),
            shiping_query_from_street  : $('#shiping_query_from_street').val(),
            shiping_query_from_city    : $('#shiping_query_from_city').val(),
            shiping_query_from_state   : $('#shiping_query_from_state').val(),
            shiping_query_from_zip     : $('#shiping_query_from_zip').val()
        }

        $.get('/wp-json/gf-roadie/v1/estimate/' + $('#shipment_entry_id').val(), data, response => {
            response = JSON.parse(response)
            $('#shiping_estimate_response').html(
                '<div><strong>Price:</strong> $' + response.price + '</div>' +
                '<div><strong>Distance:</strong> ' + response.estimated_distance + 'mi</div>'
            )
            
            $('#shiping_estimate').slideDown()
        })
        button_address_estimate
    })


    $('#gf_roadie_save').on( 'click', e => {
        e.preventDefault()

        let checked = 'false'
        if( $("#shipment_enabled").is(':checked')) {
            checked = 'true'
        }

        e.target.disabled = true

        $.get('/wp-json/gf-roadie/v1/delivery/' + $('#shipment_entry_id').val() + '?enabled=' + checked, data => {
            e.target.disabled = false
        })

    })
})