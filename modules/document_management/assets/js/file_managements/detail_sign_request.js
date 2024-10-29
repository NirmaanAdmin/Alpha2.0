	var hidden_columns = [0];
	var wrapper = document.getElementById("identityConfirmationForm");
	var canvas = document.getElementById("signature");
	var clearButton = wrapper.querySelector("[data-action=clear]");
	var undoButton = wrapper.querySelector("[data-action=undo]");
	var identityFormSubmit = document.getElementById('identityConfirmationForm');
	var signaturePad = new SignaturePad(canvas, {
		maxWidth: 2,
		onEnd:function(){
			signaturePadChanged();
		}
	});

	(function(){
		"use strict";

		SignaturePad.prototype.toDataURLAndRemoveBlanks = function() {
			var canvas = this._ctx.canvas;
	// First duplicate the canvas to not alter the original
	var croppedCanvas = document.createElement('canvas'),
	croppedCtx = croppedCanvas.getContext('2d');

	croppedCanvas.width = canvas.width;
	croppedCanvas.height = canvas.height;
	croppedCtx.drawImage(canvas, 0, 0);

	// Next do the actual cropping
	var w = croppedCanvas.width,
	h = croppedCanvas.height,
	pix = {
		x: [],
		y: []
	},
	imageData = croppedCtx.getImageData(0, 0, croppedCanvas.width, croppedCanvas.height),
	x, y, index;

	for (y = 0; y < h; y++) {
		for (x = 0; x < w; x++) {
			index = (y * w + x) * 4;
			if (imageData.data[index + 3] > 0) {
				pix.x.push(x);
				pix.y.push(y);

			}
		}
	}
	pix.x.sort(function(a, b) {
		return a - b
	});
	pix.y.sort(function(a, b) {
		return a - b
	});
	var n = pix.x.length - 1;

	w = pix.x[n] - pix.x[0];
	h = pix.y[n] - pix.y[0];
	var cut = croppedCtx.getImageData(pix.x[0], pix.y[0], w, h);

	croppedCanvas.width = w;
	croppedCanvas.height = h;
	croppedCtx.putImageData(cut, 0, 0);

	return croppedCanvas.toDataURL();
	};

	clearButton.addEventListener("click", function(event) {
		signaturePad.clear();
		signaturePadChanged();
	});

	undoButton.addEventListener("click", function(event) {
		var data = signaturePad.toData();
		if (data) {
	data.pop(); // remove the last dot or line
	signaturePad.fromData(data);
	signaturePadChanged();
	}
	});
	$('#identityConfirmationForm').on('submit', function() {
		signaturePadChanged();
	});

	})(jQuery);

	function staff_sign_document(el, rel_id, id){
		"use strict";
		$('#identityConfirmationModal').modal('show');
		$('#identityConfirmationModal input[name="id"]').val(id);
		$('#identityConfirmationModal input[name="rel_id"]').val(rel_id);
		$('#identityConfirmationModal input[name="firstname"]').val($(el).data('firstname'));
		$('#identityConfirmationModal input[name="lastname"]').val($(el).data('lastname'));
		$('#identityConfirmationModal input[name="email"]').val($(el).data('email'));
	}

	function signaturePadChanged() {
		"use strict";
		var input = document.getElementById('signatureInput');
		var $signatureLabel = $('#signatureLabel');
		$signatureLabel.removeClass('text-danger');

		if (signaturePad.isEmpty()) {
			$signatureLabel.addClass('text-danger');
			input.value = '';
			return false;
		}

		$('#signatureInput-error').remove();
		var partBase64 = signaturePad.toDataURLAndRemoveBlanks();
		partBase64 = partBase64.split(',')[1];
		input.value = partBase64;
	}

