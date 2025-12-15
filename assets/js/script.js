// Document ready
$(document).ready(function() {
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);

    // Confirm before deleting
    $('.confirm-delete').on('click', function() {
        return confirm('Apakah Anda yakin ingin menghapus item ini? Tindakan ini tidak dapat dibatalkan.');
    });

    // Toggle password visibility
    $('.toggle-password').on('click', function() {
        var input = $($(this).attr('toggle'));
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).find('i').removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).find('i').removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Auto-submit forms on select change
    $('.auto-submit').on('change', function() {
        $(this).closest('form').submit();
    });

    // Character counter for textareas
    $('textarea[maxlength]').on('input', function() {
        var maxLength = $(this).attr('maxlength');
        var currentLength = $(this).val().length;
        var remaining = maxLength - currentLength;
        
        var counter = $(this).siblings('.char-counter');
        if (counter.length === 0) {
            counter = $('<small class="text-muted char-counter"></small>');
            $(this).after(counter);
        }
        
        counter.text(remaining + ' karakter tersisa');
        
        if (remaining < 10) {
            counter.addClass('text-danger');
        } else {
            counter.removeClass('text-danger');
        }
    });

    // Initialize DataTables if available
    if ($.fn.DataTable) {
        $('.datatable').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Indonesian.json"
            },
            "responsive": true,
            "pageLength": 25,
            "order": [[0, 'desc']]
        });
    }

    // Handle file upload preview
    $('.custom-file-input').on('change', function() {
        var fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').html(fileName || 'Pilih file...');
    });

    // Handle tab persistence
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('lastTab', $(e.target).attr('href'));
    });

    var lastTab = localStorage.getItem('lastTab');
    if (lastTab) {
        $('[href="' + lastTab + '"]').tab('show');
    }

    // Auto-hide success messages when clicking anywhere
    $(document).on('click', function() {
        $('.alert-success').fadeOut('slow');
    });

    // Prevent form resubmission on page refresh
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }

    // Initialize summernote if available
    if ($.fn.summernote) {
        $('.summernote').summernote({
            height: 200,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['fontname', ['fontname']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['height', ['height']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'video']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ]
        });
    }

    // Handle select2 if available
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap4',
            placeholder: 'Pilih opsi',
            allowClear: true
        });
    }

    // Handle datepicker if available
    if ($.fn.datepicker) {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            language: 'id'
        });
    }

    // Handle timepicker if available
    if ($.fn.timepicker) {
        $('.timepicker').timepicker({
            showMeridian: false,
            minuteStep: 5,
            showSeconds: false,
            showInputs: false
        });
    }

    // Handle form validation
    $('form.needs-validation').on('submit', function(event) {
        if (this.checkValidity() === false) {
            event.preventDefault();
            event.stopPropagation();
        }
        $(this).addClass('was-validated');
    });

    // Handle AJAX form submissions
    $('.ajax-form').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalBtnText = submitBtn.html();
        
        // Disable submit button and show loading state
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...');
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Handle success response
                if (response.redirect) {
                    window.location.href = response.redirect;
                } else if (response.message) {
                    showAlert('success', response.message);
                    form.trigger('reset');
                    form.removeClass('was-validated');
                }
            },
            error: function(xhr) {
                // Handle error response
                var errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                showAlert('danger', errorMessage);
            },
            complete: function() {
                // Re-enable submit button and restore original text
                submitBtn.prop('disabled', false).html(originalBtnText);
            }
        });
    });

    // Function to show alert message
    function showAlert(type, message) {
        var alertHtml = '<div class="alert alert-' + type + ' alert-dismissible fade show" role="alert">' +
            message +
            '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
            '</div>';
        
        // Prepend alert to the container or body
        $('.alert-container').prepend(alertHtml);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow', function() {
                $(this).remove();
            });
        }, 5000);
    }

    // Handle image preview for file inputs
    $('.image-preview-input').on('change', function() {
        var input = this;
        var preview = $(this).siblings('.image-preview');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                preview.attr('src', e.target.result);
                preview.removeClass('d-none');
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    });

    // Handle print button
    $('.btn-print').on('click', function() {
        window.print();
    });

    // Handle back to top button
    var backToTop = $('.back-to-top');
    $(window).scroll(function() {
        if ($(this).scrollTop() > 300) {
            backToTop.fadeIn('slow');
        } else {
            backToTop.fadeOut('slow');
        }
    });
    
    backToTop.on('click', function(e) {
        e.preventDefault();
        $('html, body').animate({scrollTop: 0}, 800);
        return false;
    });

    // Handle copy to clipboard
    $('.copy-to-clipboard').on('click', function() {
        var text = $(this).data('clipboard-text');
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();
        
        // Show tooltip
        var tooltip = new bootstrap.Tooltip(this, {
            title: 'Tersalin!',
            trigger: 'manual'
        });
        
        tooltip.show();
        
        // Hide tooltip after 1.5 seconds
        setTimeout(function() {
            tooltip.hide();
        }, 1500);
    });
});
