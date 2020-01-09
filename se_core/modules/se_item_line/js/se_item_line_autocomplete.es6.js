/**
 * @file
 * Autocomplete based on jQuery UI.
 */

(function($, Drupal) {
  let autocomplete;

  /**
   * The search handler is called before a search is performed.
   *
   * @function Drupal.autocomplete.options.search
   *
   * @param {object} event
   *   The event triggered.
   *
   * @return {boolean}
   *   Whether to perform a search or not.
   */
  function searchHandler(event) {
    // eslint-disable-next-line prefer-destructuring
    const options = autocomplete.options;

    if (options.isComposing) {
      return false;
    }

    const term = event.target.value;

    // Abort search if the first character is in firstCharacterBlacklist.
    if (
      term.length > 0 &&
      options.firstCharacterBlacklist.indexOf(term[0]) !== -1
    ) {
      return false;
    }
    // Only search when the term is at least the minimum length.
    return term.length >= options.minLength;
  }

  /**
   * JQuery UI autocomplete source callback.
   *
   * @param {object} request
   *   The request object.
   * @param {function} response
   *   The function to call with the response.
   */
  function sourceData(request, response) {
    const elementId = this.element.attr("id");

    if (!(elementId in autocomplete.cache)) {
      autocomplete.cache[elementId] = {};
    }

    /**
     * Filter through the suggestions removing all terms already tagged and
     * display the available terms to the user.
     *
     * @param {object} suggestions
     *   Suggestions returned by the server.
     */
    function showSuggestions(suggestions) {
      response(suggestions);
    }

    // Get the desired term and construct the autocomplete URL for it.
    const business = $("#edit-se-bu-ref-0-target-id").val();
    const businessNid = business.substring(
      business.indexOf("(") + 1,
      business.indexOf(")")
    );

    /**
     * Transforms the data object into an array and update autocomplete results.
     *
     * @param {object} data
     *   The data sent back from the server.
     */
    function sourceCallbackHandler(data) {
      autocomplete.cache[elementId][request.term] = data;

      // Send the new string array of terms to the jQuery UI list.
      showSuggestions(data);
    }

    // Check if the term is already cached.
    if (autocomplete.cache[elementId].hasOwnProperty(request.term)) {
      showSuggestions(autocomplete.cache[elementId][request.term]);
    } else {
      const options = $.extend(
        {
          success: sourceCallbackHandler,
          data: { q: request.term, se_bu_ref: businessNid }
        },
        autocomplete.ajax
      );
      $.ajax(this.element.attr("data-autocomplete-path"), options);
    }
  }

  /**
   * Handles an autocompletefocus event.
   *
   * @return {boolean}
   *   Always returns false.
   */
  function focusHandler() {
    return false;
  }

  /**
   * Handles an autocompleteselect event.
   *
   * @param {jQuery.Event} event
   *   The event triggered.
   * @param {object} ui
   *   The jQuery UI settings object.
   *
   * @return {boolean}
   *   Returns false to indicate the event status.
   */
  function selectHandler(event, ui) {
    event.target.value = ui.item.value;
    // Return false to tell jQuery UI that we've filled in the value already.
    return false;
  }

  /**
   * Override jQuery UI _renderItem function to output HTML by default.
   *
   * @param {jQuery} ul
   *   jQuery collection of the ul element.
   * @param {object} item
   *   The list item to append.
   *
   * @return {jQuery}
   *   jQuery collection of the ul element.
   */
  function renderItem(ul, item) {
    return $("<li>")
      .append($("<a>").html(item.label))
      .appendTo(ul);
  }

  /**
   * Attaches the autocomplete behavior to all required fields.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the autocomplete behaviors.
   * @prop {Drupal~behaviorDetach} detach
   *   Detaches the autocomplete behaviors.
   */
  Drupal.behaviors.autocomplete = {
    attach(context) {
      // Act on textfields with the "form-autocomplete" class.
      const $autocomplete = $(context)
        .find("input.form-autocomplete")
        .once("autocomplete");
      if ($autocomplete.length) {
        // Allow options to be overridden per instance.
        const blacklist = $autocomplete.attr(
          "data-autocomplete-first-character-blacklist"
        );
        $.extend(autocomplete.options, {
          firstCharacterBlacklist: blacklist || ""
        });
        // Use jQuery UI Autocomplete on the textfield.
        // eslint-disable-next-line func-names
        $autocomplete.autocomplete(autocomplete.options).each(function() {
          $(this).data("ui-autocomplete")._renderItem =
            autocomplete.options.renderItem;
        });

        $autocomplete.on("compositionstart.autocomplete", () => {
          autocomplete.options.isComposing = true;
        });
        $autocomplete.on("compositionend.autocomplete", () => {
          autocomplete.options.isComposing = false;
        });
      }
    },
    detach(context, settings, trigger) {
      if (trigger === "unload") {
        $(context)
          .find("input.form-autocomplete")
          .removeOnce("autocomplete")
          .autocomplete("destroy");
      }
    }
  };

  /**
   * Autocomplete object implementation.
   *
   * @namespace Drupal.autocomplete
   */
  autocomplete = {
    cache: {},
    // Exposes options to allow overriding by contrib.
    // jQuery UI autocomplete options.

    /**
     * JQuery UI option object.
     *
     * @name Drupal.autocomplete.options
     */
    options: {
      source: sourceData,
      focus: focusHandler,
      search: searchHandler,
      select: selectHandler,
      renderItem,
      minLength: 1,
      // Custom options, used by Drupal.autocomplete.
      firstCharacterBlacklist: "",
      // Custom options, indicate IME usage status.
      isComposing: false
    },
    ajax: {
      dataType: "json"
    }
  };

  Drupal.autocomplete = autocomplete;
})(jQuery, Drupal);
