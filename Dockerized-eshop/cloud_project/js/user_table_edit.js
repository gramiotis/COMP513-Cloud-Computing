//handle update of enabled checkbox
function check(id, elem) {
    $(document).ready(function(){
        var value;

        if($(elem).is(":checked"))
            value = 1;
        else
            value = 0;

        $.ajax({
                url: 'scripts/live_user_edit.php',
                type: 'post',
                data: { confirm_value:value, id:id },
                success:function(response){
                    console.log("User changes saved successfully");
                }
        }); 
    });       
}

//delete user from database using its id
function delete_user(id) { 
    $(document).ready(function(){
        $.ajax({
            url: 'scripts/handle_ajax.php',
            type: 'POST',
            data: {id:id},
            success: function (result) {
                    //remove user from table
                    document.getElementById(id).remove();
            },
            error: function(response){
                alert(response);
            }
        }); 
    });
}

//functions for editing user table cells and saving changes
    // Show Input element for editable cells
    $(document).on('click', '.edit', function(){
        $('.txtedit').hide();
        $(this).next('.txtedit').show().focus();
        $(this).hide();
    });

    // Save data
    $(document).on('keyup', '.txtedit', function (e){

        if (e.key == 'Enter')
        {
            // Get edit id, field name and value
            var field_name = this.id;
            var edit_id = $(this).closest('tr').attr('id');
            var value = $(this).val();
            // Hide Input element
            $(this).hide();

            // Hide and Change Text of the container with input elmeent
            $(this).prev('.edit').show();
            $(this).prev('.edit').text(value);

            $.ajax({
                url: 'scripts/live_user_edit.php',
                type: 'post',
                data: { field:field_name, value:value, id:edit_id },
                success:function(response){
                    console.log("User changes saved successfully");
                }
            });
        }
        else if(e.key == 'Escape') //if pressed cancel changes
        {
            $(this).hide();
            $(this).prev('.edit').show();
            this.value = $(this).prev('.edit').text();
        }
    });
    
    //cancel new data if clicking anywhere
    $(document).on('focusout', '.txtedit', function(){
        $(this).hide();
        $(this).prev('.edit').show();
        this.value = $(this).prev('.edit').text();
    });

    //update new role
    $(document).on('change', '#role', function() {
        var edit_id = $(this).closest('tr').attr('id');
        var value = this.value;

        $.ajax({
                url: 'scripts/live_user_edit.php',
                type: 'post',
                data: { role:'website', value:value, id:edit_id },
                success:function(response){
                    console.log(response);
                    console.log("User changes saved successfully");
                }
        });
    });
