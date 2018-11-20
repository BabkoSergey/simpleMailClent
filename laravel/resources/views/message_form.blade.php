<div class="modal fade" id="newMessage" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">                
                <h4 class="modal-title" id="myModalLabel">New Message</h4>
            </div>
            <div class="modal-body">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">                            
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <strong>Email:</strong>
                                    <input placeholder="Email" class="form-control" name="email" type="email" value="" required="">
                                </div>
                            </div>
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <strong>Subject:</strong>
                                    <input placeholder="Subject" class="form-control" name="subject" type="text" value="">
                                </div>
                            </div>                            
                            <div class="col-xs-12 col-sm-12 col-md-12">
                                <div class="form-group">
                                    <strong>Message:</strong>
                                    <textarea class="form-control" rows="10" name="body"></textarea>
                                </div>
                            </div>                            
                        </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success pull-left sendMessage">Send</button>
                <button type="button" class="btn btn-default cancelMessage" data-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')

    <script>
    $(function () {
        $(document).ready(function() {            
            $(document).on('click', '.new-mail-modal', function (e) {
                e.preventDefault();
                $('#newMessage').modal('show');
	    });
            
            $(document).on('click', '.sendMessage', function (e) {
                e.preventDefault();
                
                if(!validate($('input[name=email]'))) return;
                
                $.post("/new_message", {
                    to: $('input[name=email]').val(),
                    subject: $('input[name=subject]').val(),
                    body: $('textarea[name=body]').val(),
                    _token: $("input[name=_token]").val()
                })
                .done(function (data) {
                    $('#newMessage').modal('hide');
                    clearMessageForm();
                })
                .fail(function (xhr) {
                    alert('Message not send');
                });
                
	    });
            
            $(document).on('click', '.cancelMessage', function (e) {
                e.preventDefault();
                clearMessageForm();
	    });
            
            function validateEmail(email) {
                var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            }
            
            function validate(emailEl) {
                var noValidEmail = true;
                var email = emailEl.val();
                if (validateEmail(email)) {
                    emailEl.removeClass('is-invalid');
                } else {
                    emailEl.addClass('is-invalid');
                    noValidEmail = false;
                }
                return noValidEmail;
            }
            
            function clearMessageForm(){
                $('#newMessage').find(":text, :file, :checkbox, select, textarea, input").val('');
                $('#newMessage').find("textarea").html('');
            }
            
        });
    });
    </script>
@endpush