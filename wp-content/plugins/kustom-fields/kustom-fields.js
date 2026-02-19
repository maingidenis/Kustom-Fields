jQuery( function( $ ) {
    if ( typeof KustomFields === 'undefined' ) return;

    function getFields() {
        return {
            blockCompany: $( '[name="kustom/company_name"], [name="kustom_company_name"]' ),
            blockRole:    $( '[name="kustom/role"], [name="kustom_role"]' ),
            classicCompany: $( '#billing_kustom_company_name_field, #kustom_company_name_field' ),
            classicRole:    $( '#billing_kustom_role_field, #kustom_role_field' ),
        };
    }

    function applyToggle( hasCategory ) {
        var f = getFields();
        var blockCompanyWrap = f.blockCompany.closest( '.wc-block-components-input-control, .form-row' );
        var blockRoleWrap    = f.blockRole.closest( '.wc-block-components-input-control, .form-row' );

        if ( hasCategory ) {
            f.classicCompany.show();
            f.classicRole.show();
            blockCompanyWrap.show();
            blockRoleWrap.show();

            f.blockCompany.prop( 'required', true );
            f.blockRole.prop( 'required', true );

            var addressFirst = $( '#billing_address_1_field, #billing_address_1, .woocommerce-billing-fields__field-wrapper .wc-block-components-input-control' ).first();

            if ( addressFirst.length ) {
                f.classicCompany.insertBefore( addressFirst );
                f.classicRole.insertBefore( addressFirst );
            }

            var billingWrapper = $( '.woocommerce-billing-fields__field-wrapper' ).first();
            if ( billingWrapper.length && f.blockCompany.length ) {
                blockRoleWrap.each( function() { billingWrapper.prepend( $( this ) ); } );
                blockCompanyWrap.each( function() { billingWrapper.prepend( $( this ) ); } );
            }
        } else {
            f.classicCompany.hide();
            f.classicRole.hide();
            blockCompanyWrap.hide();
            blockRoleWrap.hide();

            f.blockCompany.prop( 'required', false );
            f.blockRole.prop( 'required', false );
        }
    }

    function checkCategory() {
        $.post( KustomFields.ajax_url, {
            action:   'kustom_check_cart_category',
            nonce:    KustomFields.nonce,
            category: KustomFields.category,
        } ).done( function( resp ) {
            applyToggle( !! ( resp && resp.success && resp.data.has_category ) );
        } ).fail( function() {
            applyToggle( false );
        } );
    }

    checkCategory();

    $( document.body ).on( 'updated_checkout updated_wc_div wc_fragments_refreshed added_to_cart', function() {
        setTimeout( checkCategory, 300 );
    } );
} );
