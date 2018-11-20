@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <a class="btn btn-success  btn-sm new-mail-modal" role="button"> {{ __('Send Mail') }}</a>                    
                </div>

                <div class="card-body">
                    @foreach ($folders as $folder)
                        <a href="{{url('/?folder='.htmlentities(urlencode($folder['path'])))}}">
                            <li>{{ $folder['name'] }} @if($folder['unseen'] > 0) ({{ $folder['unseen'] }}) @endif</li>
                        </a>
                    @endforeach                    
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">{{ $messages['folder'] }}</div>

                <div class="card-body">
                    <table class="table table-responsive-lg table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>{{$messages['from']}}</th>
                                <th>Subject</th>                                
                                <th>Message</th>                                
                                <th>Date</th>
                                <th>Attachments</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($messages['messages'] as $message)                                
                                <tr>
                                    <td>{{$message['uid']}}</td>
                                    <td>@if($messages['from'] == 'From') {{$message['from']}} @else {{$message['to']}} @endif</td>
                                    <td>
                                        <a href="{{url('/message/?folder='.htmlentities(urlencode($curFolder)).'&uid='.$message['uid'])}}">
                                            {{$message['subject']}}
                                        </a>
                                    </td>
                                    <td>{{$message['body']}}</td>
                                    <td>{{$message['date']}}</td>
                                    <td>@if($message['attachments'] > 0){{$message['attachments']}} @endif</td>                                    
                                    <td>
                                        <a class="jq_delete" href="" data-folder="{{htmlentities(urlencode($curFolder))}}" data-uid="{{$message['uid']}}">
                                            X
                                        </a>
                                    </td>
                                </tr>                            
                                
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div style="display: none">
    <form id="jq-delete-form" method="POST" action="{{ url('/message') }}" accept-charset="UTF-8">
        @csrf
        <input name="_method" type="hidden" value="DELETE">   
        <input name="folder" type="text" value="">   
        <input name="uid" type="text" value="">   
        <input type="submit" value="Delete">
    </form>
</div>

@include('message_form')

@endsection

@push('scripts')

 <script>
    $(function () {
        $(document).ready(function() {  
            
            $(document).on('click','.jq_delete',function (e){
                e.preventDefault(); 
                $('#jq-delete-form > input[name=folder]').val($(this).attr('data-folder'));
                $('#jq-delete-form > input[name=uid]').val($(this).attr('data-uid'));
                $('#jq-delete-form').submit();
            }); 

        });
    });
</script>
@endpush