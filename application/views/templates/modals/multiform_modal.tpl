<div style="float: right">
  <a href="#" data-toggle="modal" data-target="#myModal">
    <img src="/assets/images/tip_icon.png" style="width:7%;height:7%;float:right;">
  </a>
    <div id="myModal" class="modal fade multiform_modal" role="dialog">
    <div class="modal-dialog modal-lg">
    <div class="modal-content" style="padding: 10px 20px 20px 20px; color: #505458">
      <div class="modal-header" style="text-align: center">
       
        <h4 class="modal-title">{{$title}|translate} <button type="button" class="close" data-dismiss="modal">&times;</button></h4>
      </div>
      <div class="modal-body">
        <p align="justify"style="text-indent: 5%;">{{$content}|translate}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-dismiss="modal">{{'Zamknij'}|translate}</button>
      </div>
    </div>
    </div>
    </div>
</div>
<div style="clear: both"></div>