/**
 * @author Innokenty Shuvalov
 *		 ishuvalov@pipeinpipe.info
 *		 vk.com/innocent
 */
(function() {
	/**
	 * itemId -> tagId[]
	 * @type {Array}
	 */
	window.tagIds = {};

	/**
	 * tagId -> tagValue
	 * @type {Array}
	 */
	window.tagValues = {};

	window.setTags = function (tags, itemId) {
		for (var i in tags) {
			addTag(tags[i].id, tags[i].value, itemId);
		}
	}

	window.getTagIds = function (itemId) {
		if (!itemId) itemId = 0;
		tagIds[itemId] || (tagIds[itemId] = []);
		return tagIds[itemId];
	}

	window.addTag = function (tagId, tagValue, itemId) {
		if (!itemId) itemId = 0;
		tagIds[itemId] || (tagIds[itemId] = []);
		tagIds[itemId].push(tagId);
		tagValues[tagId] = tagValue;
	}

	window.removeTag = function (tagId, itemId) {
		if (!itemId) itemId = 0;
		var newTags = [];
		for (var i in tagIds[itemId]) {
			if (!(tagIds[itemId][i] == tagId)) {
				newTags.push(tagIds[itemId][i]);
			}
		}
		tagIds[itemId] = newTags;
	}

	window.getTagValue = function (tagId) {
		return tagValues[tagId];
	}

	window.fillFormTagsInput = function(tagsInputCssSelector, itemId) {
		var tagIds = getTagIds(itemId);
		var str = '';
		for (var i in tagIds) {
			str += tagIds[i] + (i == tagIds.length - 1 ? '' : ',');
		}
		$(tagsInputCssSelector).val(str);
		return true;
	}
})();

(function() {
	const TAG_CREATOR_CSS_SELECTOR = '.tag_creator';
	const ADDED_TAGS_CSS_SELECTOR = '.tag_creator_added_tags';
	const NEW_TAG_SELECTOR_CSS_SELECTOR = '.tag_creator_new_tag_selector';
	const CREATE_BTN_CSS_SELECTOR = '.tag_creator_btn_create';
	const ADDED_TAG_CLASS = 'tag_creator_added_tag';

	const ANIMATION_SPEED = 'fast';

	const ITEM_ID_ATTR_NAME = "data-item-id";
	const WIDTH_ATTR_NAME = "data-width";
	const DEFAULT_TAG_SELECTOR_WIDTH = 300;

	const AJAX_PROC_URL = '/procs/proc_tag_creator.php';

	const JSON_METHOD_GET_TAG_SUGGESTIONS = 'get_tag_suggestions';
	const JSON_METHOD_ADD = 'add_tag';
	const JSON_METHOD_REMOVE = 'remove_tag';
	const JSON_METHOD_CREATE = 'create_tag';

	const JSON_TAG_VALUE_KEY = 'tag_value';
	const JSON_TAG_ID_KEY = 'tag_id';
	const JSON_SUGGESTIONS_KEY = 'tags';

	$(document).ready(function () {
		$(TAG_CREATOR_CSS_SELECTOR).each(function() {

			//this = tag creator outer container
			var itemId = $(this).attr(ITEM_ID_ATTR_NAME);
			if (!itemId) itemId = 0;

			var width = $(this).attr(WIDTH_ATTR_NAME);
			width = parseInt(width);
			if (!width) width = DEFAULT_TAG_SELECTOR_WIDTH

			var addedTagsDiv = $(ADDED_TAGS_CSS_SELECTOR, this);
			showAddedTags();

			var tagSelectorDiv = $(NEW_TAG_SELECTOR_CSS_SELECTOR, this);
			var createBtn = $(CREATE_BTN_CSS_SELECTOR, this);
			buildDynamicSelector();


			function showAddedTags() {
				var localTagIds = getTagIds(itemId);
				var id;
				for (var i in localTagIds) {
					id = localTagIds[i];
					showTag(id, getTagValue(id));
				}
			}

			function showTag(tagId, value) {
				buildTag(value, tagId)
						.hide()
						.appendTo(addedTagsDiv)
						.slideDown(ANIMATION_SPEED);
			}

			function buildTag(value, tagId) {
				return $('<div/>', {
					text: value,
					title: 'удалить'
				})
						.addClass(ADDED_TAG_CLASS)
						.click(function () {
							tagRemove($(this), tagId);
						});
			}

			function buildDynamicSelector() {
				sendAjax({
					method: JSON_METHOD_GET_TAG_SUGGESTIONS
				}, function (json) {
					var tagsString = json[JSON_SUGGESTIONS_KEY];
					if (!tagsString) {
						console.debug('Unable to get tags via ajax request!');
						return;
					}

					var tagSelector = (new DynamicSelector({
						content:$.parseJSON(tagsString),
						onSelect:function (tagId) {
							tagAdd(tagId);
							tagSelector.clear();
						}
					}))
							.setWidth(width)
							.appendTo(tagSelectorDiv);

					createBtn.click(function () {
						tagCreate(tagSelector.text());
					});
				});
			}

			function tagAdd(tagId) {
				sendAjax({
					method: JSON_METHOD_ADD,
					data: tagId,
					item_id: itemId
				}, function (json) {
					var tagValue = json[JSON_TAG_VALUE_KEY];
					showTag(tagId, tagValue);
					addTag(tagId, tagValue, itemId);
				});
			}

			function tagRemove(tagDiv, tagId) {
				sendAjax({
					method: JSON_METHOD_REMOVE,
					data: tagId,
					item_id: itemId
				}, function () {
					tagDiv.slideUp(ANIMATION_SPEED);
					removeTag(tagId, itemId);
				});
			}

			function tagCreate(value) {
				if (value) {
					sendAjax({
						method: JSON_METHOD_CREATE,
						data: value,
						item_id: itemId
					}, function (json) {
						var tagId = json[JSON_TAG_ID_KEY];
						showTag(tagId, value);
						addTag(tagId, value, itemId);
					});
				}
			}

			function sendAjax(data, handlerFunction) {
				$.ajax({
					url: AJAX_PROC_URL,
					data: data,
					dataType: 'json',
					success: function (json) {
						if (json.status) {
							handlerFunction(json)
						} else {
							alert('Извините, при отправке данных произошла неведомая ошибка. Попробуйте ещё раз!');
							console.debug(json);
						}
					}
				});
			}
		});
	});
})();
