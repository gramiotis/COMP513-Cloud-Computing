//handle update fro soldout checkbox
function check(id, elem) {
    $(document).ready(function(){
        var value;

        if($(elem).is(":checked"))
            value = 1;
        else
            value = 0;

        $.ajax({
                url: 'scripts/live_product_edit.php',
                type: 'post',
                data: { field:'soldout', value:value, id:id },
                success:function(response){
                    console.log("User changes saved successfully");

                    $.ajax({
                        url: 'scripts/orion/handle_sub.php',
                        type: 'post',
                        data: { entityid_update:id, soldout:value },
                        success:function(response){
                            console.log("Entity changed successfully");
                        }
                }); 
                }
        }); 
    });       
}

function delete_product(id) { 
    $(document).ready(function(){
        $.ajax({
            url: 'scripts/handle_ajax.php',
            type: 'POST',
            data: {product_id:id},
            success: function (result) {
                    //remove product from table
                    document.getElementById(id).remove();

                    $.ajax({
                        url: 'scripts/orion/handle_sub.php',
                        type: 'POST',
                        data: {entityid_delete:id},
                        success: function (result) {
                                //remove product from table
                                console.log('Entity deleted');
                                console.log(result);
                        }
                    }); 
            }
        }); 
    });
}
//display add new entry form
function show_form(){
    if(document.getElementById("new_entry").style.display=="block"){
        document.getElementById("new_entry").style.display="none";
        document.getElementById("add-btn").style.display = "block";
    }else{
        document.getElementById("new_entry").style.display="block";
        document.getElementById("add-btn").style.display = "none";
    }
}

    //handle add new entry form
    function add_entry(){
        var name = $("#name").val();
        var code = $("#product_code").val();
        var price = $("#price").val();
        var datetime = $("#datetime").val();
        var category = $("#category").val();
        // Returns successful data submission message when the entered information is stored in database.
        var dataString = 'name_new='+ name + '&code_new='+ code + '&price_new='+ price + '&datetime_new=' + datetime + '&category_new=' + category;

        if(name==''||code==''||price==''||category=='')
        {
            alert("Please Fill All Fields");
        }
        else
        {
            // AJAX Code To Submit Form.
            $.ajax({
                type: "POST",
                url: "scripts/handle_ajax.php",
                data: dataString,
                success: function(result){
                    $("#new_entry")[0].reset();
                    show_form(); 
                    reload_Table();   
                }
            });
        }
        return false;
    }

    //add new row for new entry and reload table to make whole table editable
    function reload_Table(){
        $.ajax({
            type: 'GET',
            url: 'scripts/handle_ajax.php',
            dataType: 'json',
            cache: false,
            success: function(response) {
                console.log(response);

                // This will add the new row to the table
                var row = '<tr id="' + response.id + '"><td>' + response.id + '</td><td><div class="edit" >'
                + response.name + '</div><input type="text" class="txtedit" value="' 
                + response.name + '" id="NAME"></td><td><div class="edit" >' + response.code + '</div><input type="text" class="txtedit" value="' 
                + response.code + '" id="PRODUCTCODE"></td><td><div class="edit" >' + response.price + '</div><input type="text" class="txtedit" value="' 
                + response.price + '" id="PRICE"></td><td><input type="datetime-local" id="DATEOFWITHDRAWAL" name="date_of_withdrawal" value="' 
                + response.date + '" min="2020-11-11T19:30" max="2024-06-14T00:00"></td><td><div class="edit" >' 
                + response.category + '</div><input type="text" class="txtedit" value="' 
                + response.category + '" id="CATEGORY"></td><td style ="text-align: middle;><div class="edit" ></div><label class="switch">'
                +'<input type="checkbox" id="confirmed" onclick="check(\''+response.id+'\', this)"><span class="slider round"></span></label></td>'
                +'<td style ="text-align: center;vertical-align: middle";><button onclick="delete_product(\''+ response.id +'\')">Delete</button></td></tr>';

                $("table tbody").append(row);

                var id = response.id;
                
                $.ajax({
                    type: "POST",
                    url: "scripts/orion/handle_sub.php",
                    data: {entityid_new:id},
                    success: function(result){
                        console.log("Entity created!!");    
                    }
                });
            }
        });
    }

/********* Functions for editing user table cells ************/
// Show Input element
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

        console.log(field_name + " "+edit_id+" "+value);

        // Hide and Change Text of the container with input elmeent
        $(this).prev('.edit').show();
        $(this).prev('.edit').text(value);

        $.ajax({
            url: 'scripts/live_product_edit.php',
            type: 'post',
            data: { field:field_name, value:value, id:edit_id },
            success:function(response){
                console.log("User changes saved successfully");
            }
        });
    }
    else if(e.key == 'Escape')
    {
        $(this).hide();
        // Hide and Change Text of the container with input elmeent
        $(this).prev('.edit').show();
        this.value = $(this).prev('.edit').text();
    }
});

$(document).on('focusout', '.txtedit', function(){
    $(this).hide();
    // Hide and Change Text of the container with input elmeent
    $(this).prev('.edit').show();
    this.value = $(this).prev('.edit').text();
});

//update date of withdrawal
$(document).on('change', '#DATEOFWITHDRAWAL', function() {
    var edit_id = $(this).closest('tr').attr('id');
    var value = this.value;
    console.log(value);

    $.ajax({
            url: 'scripts/live_product_edit.php',
            type: 'post',
            data: { field:'DATEOFWITHDRAWAL', value:value, id:edit_id },
            success:function(response){
                console.log("User changes saved successfully");
            }
    });
});