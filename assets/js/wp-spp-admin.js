/*
 * Selectable Post and Page
 * Admin Scripts
 * Author: HappyMox
 */
/* global ajaxurl */

jQuery( document ).ready( function( $ ) {
    var saved_posts = '';
    $( 'body' ).on( 'click', 'input.post-types', function(){
        //e.preventDefault();
        var post_type = $( this ).val();
        var parent_p = $( this ).closest( 'p.spp-post-types' );
        var parent_next_p = $( parent_p ).next( 'p' );
        var widget_id = $( parent_next_p ).find( 'input.my_id' ).val();
        var splited = widget_id.split( ',' );
        var rlt_cont = '.selectablepost-' + splited[0] + splited[1];
        var order = $( parent_p ).next().next().next( 'p' ).find( 'select').val();
        var orderby = $( parent_p ).next().next( 'p' ).find( 'select').val();
        saved_posts = $( parent_p ).find( 'input[type=hidden]' ).val();
        
        if ( post_type === 'Page' ) {
            $( parent_next_p ).slideUp();
            get_selected_data( false, widget_id, rlt_cont, orderby, order, saved_posts );
        }
        
        if ( post_type === 'Post' ) {
            var cat_val = $( parent_next_p ).find( 'select' ).val();
            $( parent_next_p ).slideDown();
            get_selected_data( cat_val, widget_id, rlt_cont, orderby, order, saved_posts );
        }
    });
    
    $( 'body' ).on( 'change', 'select', function( e ){
        e.preventDefault();
        var id = $( this ).attr( 'id' );
        var parent_p, cat_select, order_select, orderby_select, 
        widget_id, splited, rlt_cont, post_type;
        if ( id.indexOf( 'orderby' ) !== -1) {
            parent_p = $( this ).closest( 'p' );
            cat_select = $( parent_p ).prev( 'p' ).find( 'select.postform' ).val();
            order_select = $( parent_p ).next( 'p' ).find( 'select' ).val();
            orderby_select = $( this ).val();
            widget_id = $( '#' + id.replace( 'orderby', 'my_id' ) ).val();
            saved_posts = $( '#' + id.replace( 'orderby', 'saved_posts' ) ).val();
            splited = widget_id.split( ',' );
            rlt_cont = '.selectablepost-' + splited[0] + splited[1];
            post_type = $( '#' + id.replace( 'orderby', 'saved_posts' ) ).parent('p').children( 'input[type=radio]:checked').val();
            
            var type = (post_type === 'Post') ? cat_select : false;
            get_selected_data( type, widget_id, rlt_cont, orderby_select, order_select, saved_posts );
        }
        
        if ( id.indexOf( 'order_type' ) !== -1) {
            parent_p = $( this ).closest( 'p' );
            cat_select = $( parent_p ).prev().prev( 'p' ).find( 'select.postform' ).val();
            orderby_select = $( parent_p ).prev( 'p' ).find( 'select' ).val();
            order_select = $( this ).val();
            widget_id = $( '#' + id.replace( 'order_type', 'my_id' ) ).val();
            saved_posts = $( '#' + id.replace( 'order_type', 'saved_posts' ) ).val();
            splited = widget_id.split( ',' );
            rlt_cont = '.selectablepost-' + splited[0] + splited[1];
            post_type = $( '#' + id.replace( 'order_type', 'saved_posts' ) ).parent('p').children( 'input[type=radio]:checked').val();
            
            var type = (post_type === 'Post') ? cat_select : false;
            get_selected_data( type, widget_id, rlt_cont, orderby_select, order_select, saved_posts );
        }
    });
    
    function get_selected_data(cat, w_id, container, by, order, saved_posts) {
        var what_type = ( cat === false ) ? 'page' : 'post';
        $.get(ajaxurl, {
            action: 'wp_spp_category_result_edit_form',
            cat: cat,
            id: w_id,
            post_type: what_type,
            orderby: by,
            order: order,
            saved_posts: saved_posts
        }, function( d ) {
            $( container ).empty().html(d);
        });
    }

    $( 'body' ).on( 'change', '.postform', function( e ){
        e.preventDefault();
        var selected_cat = $( this ).find( ":selected" ).val();
        var widget_id = $( this ).closest( "p" ).find( ".my_id" ).val();
        var splited = widget_id.split( ',' );
        var selecter = 'widget-' + splited[1] + '[' + splited[0] + '][cat]';
        var container = '.selectablepost-' + splited[0] + splited[1];
        var orderby = $( this ).closest( "p" ).next( 'p' ).find( 'select' ).val();
        var order = $( this ).closest( 'p' ).next().next( 'p' ).find( 'select' ).val();
        saved_posts = $( this ).closest( 'p' ).prev( 'p' ).find( 'input[type=hidden]' ).val();
        if( selecter === $( this ).attr( 'id' ) ){
            get_selected_data(selected_cat, widget_id, container, orderby, order, saved_posts);
        }
    });
    
    $( 'body' ).on( 'change', 'input[type=checkbox]', function( e ){
        var id = $( this ).attr( 'id' );
        if ( id.indexOf( 'random_post' ) !== -1) {
            if (  $(this).is(':checked') ) {
                $( ".spp-result-list" ).slideUp();
                $( ".spp-random-post-num" ).slideDown();
            } else {
                $( ".spp-result-list" ).slideDown();
                $( ".spp-random-post-num" ).slideUp();
            }
        }
    });
    
    $( 'body' ).on( 'click', 'table.spp-template-layout td', function( e ){
        var kv = $( this ).attr( 'key-value' );
        var p = $( this ).closest( 'table' ).prev( 'p' );
        var inp = $( p ).find( 'input' );
        
        /*console.log( 'Key value: ' + kv );
        console.log( 'table attr: ' + inp.attr('name') );*/
        
        $( inp ).val( kv );

        $( this ).closest( 'table' ).find( 'td' ).removeClass( 'selected' );
        $( this ).addClass( 'selected' );
        
        e.preventDefault();
    });
});
