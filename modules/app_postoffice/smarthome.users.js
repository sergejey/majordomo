$('.confirm-delete').on('click', function (e)
{
   e.preventDefault();
   var user_id = $(this).data('userid');
   modal = $('#UserRemoveModal');

   modal.find('.btn-danger').click(function ()
   {
      $.post("/content/actions/configuration_users.php",
      {
         act: "del",
         id: user_id
      }).done(function (data)
      {
         window.location.reload("/index.php?cat=configuration&action=users");
      });
   });
});

$('.confirm-device-delete').on('click', function (e)
{
   e.preventDefault();
   var device_id = $(this).data('device');
   modal = $('#DeviceRemoveModal');

   modal.find('.btn-danger').click(function ()
   {
      $.post("/content/actions/configuration_devices.php",
      {
         act: "del",
         id: device_id
      }).done(function (data)
      {
         window.location.reload("/index.php?cat=configuration&action=devices");
      });
   });
});

$('.confirm-track-delete').on('click', function (e)
{
   e.preventDefault();
   var track_id = $(this).data('track');
   modal = $('#PostOfficeTrackRemoveModal');

   modal.find('.btn-danger').click(function ()
   {
      $.post("/content/actions/application_postoffice.php",
      {
         act: "del",
         id: track_id
      }).done(function (data)
      {
         window.location.reload("/admin/index.php?cat=application&action=postoffice");
      });
   });
});

$('.track-check').on('click', function (e)
{
   e.preventDefault();
   
   $.post("/content/actions/application_postoffice.php",
   {
      act: "check",
   }).done(function (data) 
   {
      alert(data);
      //window.location.reload("/admin/index.php?cat=application&action=postoffice");
   });
});