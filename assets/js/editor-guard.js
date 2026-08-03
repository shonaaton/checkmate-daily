(function (wp, config) {
  if (!wp || !wp.data || !window.fetch || !config) return;

  var lastSeen = null;
  var lastSent = null;
  var wasSaving = false;
  var hasInitialized = false;
  var suppressZeroUntil = 0;
  var timer = null;
  var retryTimer = null;

  function getEditor() {
    return wp.data.select("core/editor");
  }

  function getPostId() {
    var editor = getEditor();
    return editor && editor.getCurrentPostId
      ? parseInt(editor.getCurrentPostId(), 10) || 0
      : 0;
  }

  function getFeaturedMedia() {
    var editor = getEditor();
    if (!editor || !editor.getEditedPostAttribute) return null;
    var value = editor.getEditedPostAttribute("featured_media");
    if (value === undefined || value === null) return null;
    value = parseInt(value, 10);
    return isNaN(value) ? null : value;
  }

  function persistFeaturedMedia(postId, mediaId, source, attempt) {
    var body = new window.URLSearchParams();
    body.set("action", "cd_set_featured_image");
    body.set("nonce", config.nonce);
    body.set("post_id", String(postId));
    body.set("attachment_id", String(mediaId));
    body.set("source", source);

    window
      .fetch(config.ajaxurl, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: body.toString(),
      })
      .then(function (response) {
        if (!response.ok) {
          throw new Error("Featured image save returned HTTP " + response.status);
        }
        return response.json();
      })
      .then(function (payload) {
        var storedId = payload && payload.data
          ? parseInt(payload.data.stored_id, 10) || 0
          : -1;
        if (!payload || !payload.success || storedId !== mediaId) {
          throw new Error("Featured image save was not confirmed");
        }
      })
      .catch(function () {
        if (attempt >= 2) return;
        window.clearTimeout(retryTimer);
        retryTimer = window.setTimeout(function () {
          persistFeaturedMedia(
            postId,
            mediaId,
            source + "_retry",
            attempt + 1,
          );
        }, 700 * (attempt + 1));
      });
  }

  function sendFeaturedMedia(source) {
    var postId = getPostId();
    var mediaId = getFeaturedMedia();
    if (!postId || mediaId === null) return;
    if (mediaId <= 0 && source === "after_save") return;
    if (mediaId === lastSent && source !== "after_save") return;

    lastSent = mediaId;
    window.clearTimeout(retryTimer);
    persistFeaturedMedia(postId, mediaId, source, 0);
  }

  function scheduleSend(source) {
    window.clearTimeout(timer);
    timer = window.setTimeout(function () {
      sendFeaturedMedia(source);
    }, 350);
  }

  wp.data.subscribe(function () {
    var editor = getEditor();
    var isSaving = !!(
      editor &&
      editor.isSavingPost &&
      editor.isSavingPost()
    );
    var justFinishedSaving = wasSaving && !isSaving;
    if (justFinishedSaving) suppressZeroUntil = Date.now() + 1500;

    var mediaId = getFeaturedMedia();
    if (mediaId !== null && mediaId !== lastSeen) {
      lastSeen = mediaId;
      if (
        (hasInitialized || mediaId > 0) &&
        !(mediaId <= 0 && (isSaving || Date.now() < suppressZeroUntil))
      ) {
        scheduleSend("editor_change");
      }
    }
    hasInitialized = true;

    if (justFinishedSaving) scheduleSend("after_save");
    wasSaving = isSaving;
  });
})(window.wp, window.cdFeaturedImageGuard);

(function (wp, config) {
  if (!wp || !wp.apiFetch || !wp.apiFetch.use || !config || !config.restFallback) {
    return;
  }

  function isPostWrite(options) {
    var method = String((options && options.method) || "GET").toUpperCase();
    var path = String((options && options.path) || "");
    return (
      (method === "POST" || method === "PUT" || method === "PATCH") &&
      /^\/wp\/v2\/posts\/\d+(?:\?.*)?$/.test(path)
    );
  }

  function shouldRetry(error) {
    if (!error || !error.code) return true;
    return error.code === "invalid_json" || error.code === "http_request_failed";
  }

  function fallbackOptions(options) {
    var retry = Object.assign({}, options);
    var parts = String(options.path || "").split("?");
    retry.url = config.restFallback + encodeURIComponent(parts.shift());
    if (parts.length) retry.url += "&" + parts.join("?");
    delete retry.path;
    return retry;
  }

  wp.apiFetch.use(function (options, next) {
    if (!isPostWrite(options)) return next(options);
    return Promise.resolve(next(options)).catch(function (error) {
      if (!shouldRetry(error)) throw error;
      return next(fallbackOptions(options));
    });
  });
})(window.wp, window.cdFeaturedImageGuard);

(function (wp, config) {
  if (
    !wp ||
    !wp.apiFetch ||
    !wp.apiFetch.use ||
    !window.fetch ||
    !config ||
    !config.locationNonce
  ) {
    return;
  }

  function getLocationTaxonomy(options) {
    if (!options || String(options.method || "GET").toUpperCase() !== "POST") {
      return "";
    }
    var route = String(options.path || options.url || "");
    var match = route.match(/\/wp\/v2\/(chess_country|chess_state)(?:\?|$)/);
    return match ? match[1] : "";
  }

  function createLocationFallback(taxonomy, data) {
    var body = new window.URLSearchParams();
    body.set("action", "cd_create_location_term");
    body.set("nonce", config.locationNonce);
    body.set("taxonomy", taxonomy);
    body.set("name", String((data && data.name) || ""));
    body.set("parent", String((data && data.parent) || 0));

    return window
      .fetch(config.ajaxurl, {
        method: "POST",
        credentials: "same-origin",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        body: body.toString(),
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.success || !payload.data || !payload.data.term) {
          throw new Error(
            (payload && payload.data && payload.data.message) ||
              "Could not save the location.",
          );
        }
        return payload.data.term;
      });
  }

  wp.apiFetch.use(function (options, next) {
    var taxonomy = getLocationTaxonomy(options);
    if (!taxonomy) return next(options);
    return Promise.resolve(next(options)).catch(function (error) {
      return createLocationFallback(taxonomy, options.data || {}).catch(function () {
        throw error;
      });
    });
  });
})(window.wp, window.cdFeaturedImageGuard);
