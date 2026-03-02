<style type="text/css">
	.event {
		background: #e9ecef;
		border-radius: 5px;
		min-height: 40px; 
		width: 100%;
		margin: 0.5% 1%;
		padding: 1% 2%;
	}
	.badge {
	   font-size: 12pt;
	}
	.reminder {
	   position: fixed;
	   top: 105%;
	   left: 50%;
	   padding: 5px 10px;
	   border-radius: 10px;
	   background: #bee5eb;
	   color: #0c5460;
	   font-weight: bold;
	   cursor: pointer;
	   border: 2px solid white;
	   user-select: none;
	   -webkit-user-select: none; /* webkit (safari, chrome) browsers */
       -moz-user-select: none; /* mozilla browsers */
       -khtml-user-select: none; /* webkit (konqueror) browsers */
       -ms-user-select: none; /* IE10+ */
       -webkit-animation: bounce 2s infinite;
       z-index: 10;
	}
	.reminder:hover {
	   border: 2px solid #0c5460;
	}
	.reminder:active {
	   color: #bee5eb;
	   background: #0c5460;
	   border-color: white;
	}
	
	@-webkit-keyframes bounce {
      0% {
        transform: translate(-50%, 0px);
      }
      
      80%{
        transform: translate(-50%, 0px); 
      }
    
      90%{
        transform: translate(-50%, -15px); 
      }
      
     100% {
        transform: translate(-50%, 0px);
      }
    }
</style>
<script type="text/javascript">
	$(function() {
		var selected = 'list-group-item-success';
		var height = window.innerHeight;
		var rem_top = (height + 40) + 'px';
		
		var checkForm = function(event) {
			var rect = $('form').get(0).getBoundingClientRect();
			if(rect.top < height) {
				$('.reminder').addClass('d-none');
			} else {
				$('.reminder').removeClass('d-none');
			}
		}

		$('[event]').click(function(ev) {
			var val = $(this).attr('event');
			
			switch(val) {
				<?php foreach($events as $event_id => $event) : ?>
				case '<?php echo $event_id ?>':
					$('#event-name').html('<?php echo $event['name'] ?>');
					$('#event-date').html('<?php echo $event['schedule'] ?>');
					$('#event-desc').html('<?php echo preg_replace("/[\n\r]/", "<br />", $event['description']) ?>');
					$('#event-venue').html('<?php echo preg_replace("/[\n\r]/", "<br />", $event['venue']) ?>');
					break;
				<?php endforeach ?>
			}
			
			var cl = 'list-group-item-info';

			$('[event]').removeClass(cl);
			$(this).addClass(cl);			
			checkForm(ev);

		}).find('.badge').click(function(ev) {
			ev.preventDefault();
			var val = $(this).closest('[event]').attr('event');
			if($(this).is('.badge-info')) {
				$(this).removeClass('badge-info').addClass('badge-success').html('Selected');
				$('form').append('<input type="hidden" class="eventsel" name="data[EventSignup][event][]" value="' + val  + '" />');
				$('.alert').remove();
				$('.reminder').animate( { top : (height - 45) + 'px' }, 200);
			} else {
				$(this).removeClass('badge-success').addClass('badge-info').html('Click to Select');
				$('form input.eventsel[value="' + val + '"]').remove();
				if(!$('form input.eventsel').length) {
					$('.reminder').animate( { top : rem_top }, 200);
				}
			}
		});

		$('form').submit(function(ev) {
			if(!$(this).find('input.eventsel').length) {
				var $events = $('#event-list');
				var pos = $events.offset();
				$(window).scrollTop(pos.top - 150);
				$('<div>').html('Please select at least 1 event').addClass('alert alert-danger row').insertBefore($events);
				ev.preventDefault();
				return;
			}
			$('button').attr('disabled', 'disabled');
		});

		$(window).scroll(checkForm);
		
		$('.reminder').click(function(event) {
			var rect = $('form').get(0).getBoundingClientRect();
			$('html,body').stop().animate({ scrollTop : rect.top }, 500, 'swing', function() {
				$('button[type=submit]').click();
				$('input[type=string]').first().focus();
			});
		}).css('top', rem_top);
		
		$('script').remove();
		$('[event]').first().click();
		
	})
</script>
<div class="jumbotron">
	<h1 class="display-4">Welcome to <a href="http://brownstone-asiatech.com" target="_blank"><img src="/img/logotrans.png" alt="Brownstone Logo" title="Click to visit our website!" style="margin-top:-20px" /></a></h1>
	<p class="lead">This is the <a href="http://brownstone-asiatech.com" target="_blank" title="Click to visit our website!">Brownstone Asia-Tech, Inc.</a> Events and Materials Sign Up Sheet</p>
  	<hr class="my-4">
</div>
<div class="container-fluid">
	<div class="row">
		<div class="col-xs text-muted small">Please select an event to view the details</div>
	</div>
	<div class="row" id="event-list">
		<div class="col-sm list-group">
			<?php foreach($select as $event_id => $name) : ?>
				<div class="list-group-item list-group-item-action" event="<?php echo $event_id ?>"><?php echo $name ?><a class="badge badge-info float-right" href="#">Click to Select</a></div>
			<?php endforeach ?>
		</div>
		<div class="col-sm event-info">
			<div class="row">
				<div class="col-lg event"><strong id="event-name"></strong></div>
			</div>
			<div class="row">
				<div class="col-lg event" id="event-date"></div>
			</div>
			<div class="row">
				<div class="col-sm event" id="event-desc"></div>
			</div>
			<div class="row">
				<div class="col-sm event" id="event-venue"></div>
			</div>
		</div>
	</div>
</div>
<div class="reminder">Click to Complete Registration</div>
<hr class="my-4">
<form method="POST">
<?php 
	foreach($data['data']['type']['Detail'] as $detail) {
	    if(!$detail['write']) continue;
		echo MahoForm::input($detail);	
	}
	echo MahoForm::submit('Submit')
?>
</form>
<div class="col-xs text-muted small">By submitting this form, I hereby CONSENT to let Brownstone Asia-Tech, Inc. to use the information I am providing for their sales and marketing purposes. As such, I also CONSENT to allow them to contact me through the channels I have provided above.</div>
