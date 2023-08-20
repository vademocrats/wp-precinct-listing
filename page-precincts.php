<?php
global $wpdb;

// Check if the user has permission to access the form
if (!has_access_to_precinct_form()) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

$table = new Custom_Precincts_Table();
$table->prepare_items();

?>
<div class="wrap">
    <!-- Admin form -->
    <div class="wrap">
    <h1>Precincts Listing</h1>
    <p>To display the precinct listing, please use shortcode [custom_precincts]</p>

    <!-- Admin form -->
    <div class="accordion" id="addUpdatePrecinctInterface">
        <div class="accordion-item">
            <div class="accordion-header">
                <strong></strong>

                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                Add / Update Precinct
              </button>
            </div>

            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#addUpdatePrecinctInterface">

                <div class="accordion-body">
                    <form id="precinct-form">
                        <input id="entry_id" type="hidden" name="entry_id" value="">
                        <input id="action" type="hidden" name="action" value="add">
                        
                        <div class="row" style="margin-bottom:10px;">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precinct_number">Number<span class="required">*</span>:</label>
                                    <input id="precinct_number" type="number" class="form-control" name="precinct_number" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precinct_name">Name<span class="required">*</span>:</label>
                                    <input id="precinct_name" type="text" class="form-control" name="precinct_name" required>
                                </div>
                            </div>                          
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precinct_location">Voting Location<span class="required">*</span>:</label>
                                    <input id="precinct_location" type="text" class="form-control" name="precinct_location" required>
                                </div>
                            </div>
                        </div>

                        <div class="row" style="margin-bottom:10px;">                            
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precinct_captain">Captain<span class="required">*</span>:</label>
                                    <input id="precinct_captain" type="text" class="form-control" name="precinct_captain" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                              <div class="form-group">
                                    <label for="precinct_deputy">Deputy:</label>
                                    <input id="precinct_deputy" type="text" class="form-control" name="precinct_deputy">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="precinct_region">Region:</label>
                                    <select id="precinct_region" class="form-select" name="precinct_region" style="min-height:34px !important;">
                                        <option value="east">East</option>
                                        <option value="central">Central</option>
                                        <option value="west">West</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <a type="button" class="submit-precinct-info btn btn-primary">Submit</a>
                                <a type="button" class="reset-form btn btn-secondary">Clear</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <hr>

    <!-- Display existing entries -->
    <div class="card" style="padding: 0px !important; max-width: 100% !important;">
        <h5 class="card-header">Precincts</h5>               
        <div class="card-body">
            <?php $table->display(); ?>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    
    $('.submit-precinct-info').on('click', function(e) {
        // Determine the action to take
        var form = $('#precinct-form');
        var action = $('#action').val();
        switch (action) {
            case 'add':
                addPrecinct();
                break;
            case 'update':
                updatePrecinct();
                break;
        }
    });

    $('.delete-precinct').on('click', function(e) {
        e.preventDefault();
        if (confirm('Are you sure you want to delete this precinct?')) {
            var entry_id = $(this).data('entry-id');
            var nonce = $(this).data('nonce');
            deletePrecinct(entry_id,nonce);
        }
    });

    $('.edit-precinct').on('click', function(e) {
        e.preventDefault();
        var entry_id = $(this).data('entry-id');
        getPrecinctData(entry_id);
        var accordion = new bootstrap.Collapse(document.getElementById('collapseOne'), { toggle: false });
        accordion.show();
    });

    $('.reset-form').on('click',function(e) {
        // Update the action parameter
        $('#action').val('add');

        // Clear form fields                
        $('#entry_id').val('');
        $('#precinct_number').val('');
        $('#precinct_name').val('');
        $('#precinct_captain').val('');
        $('#precinct_deputy').val('');
        $('#precinct_location').val('');
        $('#precinct_region').val('east');
    });

    // Add a precinct
    function addPrecinct() {
        var formData = new FormData(document.getElementById('precinct-form'));
        formData.append('action', 'add_precinct');

        $.ajax({
            type: 'POST',
            url: ajaxurl, // WordPress AJAX URL
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            success: function (response) {
                location.reload();
            }
        });
    }

    // Modify a precinct
    function updatePrecinct() {
        var formData = new FormData(document.getElementById('precinct-form'));
        formData.append('action', 'update_precinct');

        $.ajax({
            type: 'POST',
            url: ajaxurl, // WordPress AJAX URL
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            success: function (response) {
                location.reload();
            }
        });
    }

    // Delete a precinct
    function deletePrecinct(entryId,nonce) {
        $.ajax({
            type: 'POST',
            url: ajaxurl, // WordPress AJAX URL
            dataType: 'json',
            data: {
                action: 'delete_precinct',
                entry_id: entryId,
                nonce: nonce
            },
            cache: false,
            success: function (response) {
                location.reload();
            }
        });
    }

    // Populate data to edit precinct information
    function getPrecinctData(entryId) {
        $.ajax({
            type: 'GET',
            url: ajaxurl, // Replace with the actual AJAX URL
            data: {
                action: 'get_precinct_data',
                entry_id: entryId
            },
            success: function(response) {
                // Update the action parameter
                $('#action').val('update');

                // Populate form fields                
                $('#entry_id').val(response.id);
                $('#precinct_number').val(response.precinct_number);
                $('#precinct_name').val(response.precinct_name);
                $('#precinct_captain').val(response.precinct_captain);
                $('#precinct_deputy').val(response.precinct_deputy);
                $('#precinct_location').val(response.precinct_location);
                $('#precinct_region').val(response.precinct_region);
            }
        });
    }
});
</script>