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
                <div class="card-header">{{$message['subject']}}</div>

                <div class="card-header">                    
                    <p>From: {{$message['from']}}</p>
                    <p>To: {{$message['to']}}</p>
                    <p>Date: {{$message['date']}}</p>
                </div>
                <div class="card-body">
                    {{$message['body']}}
                </div>
                
                <div class="card-body">
                    <p>{{__('Attachments:')}}</p>
                    @foreach ($message['attachments'] as $attachment)                    
                        <p>{{$attachment->name}} ({{$attachment->type}})</p>
                        @if ($attachment->type == 'image')
                            <img src="{{$attachment->img_src}}" width="100%;">
                        @endif                        
                    @endforeach                     
                </div>
            </div>
        </div>
    </div>
</div>

@include('message_form')

@endsection

@push('scripts')

@endpush