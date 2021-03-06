if (!RedactorPlugins) var RedactorPlugins = {};

(function($)
{
	RedactorPlugins.imagemanager = function()
	{
		return {
			init: function()
			{
				if (!this.opts.imageManagerJson) return;

				this.modal.addCallback('image', this.imagemanager.load);
			},
			load: function()
			{
				var $modal = this.modal.getModal();

				this.modal.createTabber($modal);
				this.modal.addTab(1, 'Upload', 'active');
				this.modal.addTab(2, 'Choose');
				this.modal.addTab(3, 'Delete');

				$('#redactor-modal-image-droparea').addClass('redactor-tab redactor-tab1');

				var $tabBox2 = $('<div id="redactor-image-manager-box" style="overflow: auto; height: 300px;" class="redactor-tab redactor-tab2">').hide();
				$modal.append($tabBox2);

				$.ajax({
				  dataType: "json",
				  cache: false,
				  url: this.opts.imageManagerJson,
				  success: $.proxy(function(data)
					{
						function arraySort(a, b) {
							return new Date(b.date).getTime() - new Date(a.date).getTime();
						}

						data.sort(arraySort);

						$.each(data, $.proxy(function(key, val)
						{
							// title
							var thumbtitle = '';
							if (typeof val.title !== 'undefined') thumbtitle = val.title;

							var img = $('<img src="' + val.thumb + '" rel="' + val.image + '" title="' + thumbtitle + '" style="width: 100px; height: 75px; cursor: pointer; margin-right: 5px; margin-bottom: 5px;" />');
							$('#redactor-image-manager-box').append(img);
							$(img).click($.proxy(this.imagemanager.insert, this));

						}, this));

					}, this)
				});

				var $tabBox3 = $('<div id="redactor-image-manager-delete-box" style="overflow: auto; height: 300px;" class="redactor-tab redactor-tab3">').hide();
				$modal.append($tabBox3);

				$.ajax({
					dataType: "json",
					cache: false,
					url: this.opts.imageManagerJson,
					success: $.proxy(function(data)
					{
						function arraySort(a, b) {
							return new Date(b.date).getTime() - new Date(a.date).getTime();
						}

						data.sort(arraySort);

						$.each(data, $.proxy(function(key, val)
						{
							// title
							var thumbtitle = '';
							if (typeof val.title !== 'undefined') thumbtitle = val.title;

							var img = $('<img src="' + val.thumb + '" rel="' + val.image + '" title="' + thumbtitle + '" style="width: 100px; height: 75px; cursor: pointer; margin-right: 5px; margin-bottom: 5px;" />');
							$('#redactor-image-manager-delete-box').append(img);
							$(img).click($.proxy(this.imagemanager.delete, this));

						}, this));

					}, this)
				});

			},
			insert: function(e)
			{
				$("footer").html('');

				this.title = $(e.target).attr('title');
				this.thumb = $(e.target).attr('src');
				this.img = $(e.target).attr('rel');

				var button1 = this.modal.createActionButton('Add Thumb');
				button1.click($.proxy(this.imagemanager.addThumb, this));

				var button2 = this.modal.createActionButton('Add Image');
				button2.click($.proxy(this.imagemanager.addImage, this));
			},
			delete: function(e)
			{
				$.ajax({
					dataType: "json",
					cache: false,
					url: this.opts.imageDelete,
					data: {"filename": e.target.title},
					success: function (data)
					{
						$('#redactor-image-manager-box img[src="' + data['url'] + '"]').remove();
						$('#redactor-image-manager-delete-box img[src="' + data['url'] + '"]').remove();
					}
				});
			},
			addThumb: function()
			{
				this.image.insert('<img src="' + this.thumb + '" alt="' + this.title + '">');
			},
			addImage: function()
			{
				this.image.insert('<img src="' + this.img + '" alt="' + this.title + '">');
			}
		};
	};
})(jQuery);