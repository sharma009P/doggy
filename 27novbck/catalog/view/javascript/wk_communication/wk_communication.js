var app = angular.module('app', ['ngSanitize']);
app.controller('messageController', function($scope, $http,$timeout) {
  var formData;
  $scope.loading=true;
   $scope.expandmsg=false;
  $scope.message='';
  $scope.totalthreads='';
  $scope.total = '';
  $scope.checkboxes=[];
  $scope.message_from='';
  $scope.click = "false";
  $scope.files=[];
  $scope.messageData=[];
  $scope.formData={};
  $scope.total.inbox =0;
  $scope.total.sent = 0;
  $scope.total.trash =0;
  $scope.predicate = 'name';
  $scope.reverse = true;
  $scope.inbox =  function(){
    $scope.search = '';
    $scope.loading =true;
    $('#overlay').show();
    $scope.selection=false;
    $http.get("index.php?route=account/wk_communication/messages&p_id=1").success(function (response) {
      $timeout(function () {
     $scope.messages=response.messages;
     $scope.totalthreads=response.threadcount;
     $scope.total = response.total;
     $scope.loading =false;
    }, 1000);
    });
  }
   $scope.order = function (predicate) {
         $scope.reverse = ($scope.predicate === predicate) ? !$scope.reverse : false;
         $scope.predicate = predicate;
       };
  $scope.sent = function(){
    $scope.search = '';
    $scope.loading =true;
    $scope.selection=false;
    $http.get("index.php?route=account/wk_communication/messages&p_id=2").success(function (response) {
      $timeout(function () {
      $scope.messages=response.messages;
      $scope.totalthreads=response.threadcount;
      $scope.total = response.total;
      $scope.loading =false;
      }, 1000);
    });
  }
  $scope.drafts = function(){
    $scope.messages=[];

  }
  $scope.route = function(id) {
     $scope.count = 2;

    $scope.messageinfodata='';
    $scope.expandmsg=false;
    $scope.threadsQuery ="";
    $scope.threadsAttachments="";
    $scope.attachments="";
    $scope.message_from='';
    $scope.selection=true;
   $http.get("index.php?route=account/wk_communication/info&message_id="+id).success(function(data){
    $scope.message_info=data.query_info;
    $('#to').val(data.query_info.message_from);
    $('#parent').val(data.query_info.message_id);
    if(!angular.isUndefined(data.attachment)){
      $scope.attachments=data.attachment;
    }
    if(!angular.isUndefined(data.threads)) {
      $scope.threadsQuery =  data.threads.query_info;
      $scope.threadsAttachments = data.threads.attachment;
    }
   });
   $http.get("index.php?route=account/wk_communication/getMessageinfodata").success(function(data){
    $scope.messageinfodata = data;
    $scope.loading =false;
   });

  }
  $scope.download = function(attachment_id) {
      document.location = "index.php?route=account/wk_communication/download&attachment_id="+attachment_id;
}
  $scope.delete =function() {
    $scope.selected=[];
    for(var i=0;i<$scope.checkboxes.length;i++){
     if(!angular.isUndefined($scope.checkboxes[i])){
      if($scope.checkboxes[i].isChecked) {
      $scope.selected.push($scope.messages[i].message_id);
    }
    }
    }
    $scope.checkboxes=[];
    if($scope.selected.length>0){
    $http({
           method: "POST",
           url: 'index.php?route=account/wk_communication/delete',
           params: {
                  "message_id[]": $scope.selected
           }
        }).success(function (response){
           $timeout(function () {
          if(response.success) {
            $('.alert').remove();
            html = '<div class="alert alert-success">'+response.success+'<button class="close" data-dismiss="alert" type="button">×</button></div>';
            $(".data").prepend( html);
          }
        }, 1000);
        });
    } else {
      $('.alert').remove();
       $timeout(function () {
        html = '<div class="alert alert-danger">No Message Selected<button class="close" data-dismiss="alert" type="button">×</button></div>';
            $(".data").prepend( html);
          }, 1000);
    }
  }
  $scope.trash = function(){
    $scope.search = '';
     $scope.loading =true;
    $scope.selection=false;
    $http.get("index.php?route=account/wk_communication/messages&p_id=0").success(function (response) {
     $timeout(function () {
      $scope.messages=response.messages;
      $scope.totalthreads=response.threadcount;
      $scope.total = response.total;
       $scope.loading =false;
    }, 1000);
    });
  }
  $scope.expand = function() {
    $scope.expandmsg=false;
    $scope.count = $scope.count + 2 ;
    if($scope.count>=$scope.threadsQuery.length)
      $scope.expandmsg = true;
  }
  $scope.submitQuery = function() {
    var form = document.forms.namedItem("replyform");
    var fd= new FormData(form);
    $http.post('index.php?route=account/wk_communication/reply',fd, {
                  transformRequest: angular.identity,
                  headers: {'Content-Type': undefined},
               }).success(function(response){
                document.forms.namedItem("replyform").reset();
                if(response.success){
                   $('.alert').remove();
                    html = '<div class="alert alert-success">'+response.success+'<button class="close" data-dismiss="alert" type="button">×</button></div>';
                    $(".data").prepend( html);
                   $('#body').summernote('reset');
                $('.attach-file').css('background-image', 'none');
                $('.attach').remove();
                $('.ex').remove();
               $scope.inbox();
                }
                else if(response.error){
                  $('.alert').remove();
                   html = '<div class="alert alert-danger">'+response.error+'<button class="close" data-dismiss="alert" type="button">×</button></div>';
                   $("#content").prepend(html);
                   $('#body').summernote('reset');
                $('.attach-file').css('background-image', 'none');
                $('.attach').remove();
                $('.ex').remove();
               $scope.inbox();

               } else {
                $('.alert').remove();
                 $('#body').summernote('reset');
                $('.attach-file').css('background-image', 'none');
                $('.attach').remove();
                $('.ex').remove();
               $scope.inbox();

               }

             }).error(function(response){});
  }
  $scope.count = function(){
    if($('#inbox').hasClass('active'))
      $scope.total.inbox = $scope.filtered.length;
    if($('#trash').hasClass('active'))
      $scope.total.trash = $scope.filtered.length;
    if($('#sent').hasClass('active'))
      $scope.total.sent = $scope.filtered.length;
  }
});


app.filter('toTrusted', function ($sce) {
    return function (value) {
        return $sce.trustAsHtml(value);
    };
});
