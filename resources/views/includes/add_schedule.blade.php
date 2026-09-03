<div class="modal fade" id="addnew">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <h4 class="modal-title"><b>Add Corporate Shift Rule</b></h4>
            <div class="modal-body text-left">
                <form class="form-horizontal" method="POST" action="{{ route('schedule.store') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label for="name" class="col-sm-12 control-label">Shift Category Name <small class="text-muted">(e.g., Morning Shift, Night Shift)</small></label>
                        <div class="col-sm-12">
                            <input type="text" class="form-control" id="name" name="slug" placeholder="e.g., Morning Shift" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_in" class="col-sm-12 control-label">Expected Start Time</label>
                        <div class="col-sm-12 bootstrap-timepicker">
                            <input type="time" class="form-control timepicker" id="time_in" name="time_in" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="time_out" class="col-sm-12 control-label">Expected End Time</label>
                        <div class="col-sm-12 bootstrap-timepicker">
                            <input type="time" class="form-control timepicker" id="time_out" name="time_out" required>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default btn-flat pull-left" data-dismiss="modal"><i class="fa fa-close"></i> Close</button>
                <button type="submit" class="btn btn-primary btn-flat"><i class="fa fa-save"></i> Save Shift Rule</button>
                </form>
            </div>
        </div>
    </div>
</div>