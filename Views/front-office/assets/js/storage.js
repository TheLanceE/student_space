(function(){
  const Storage = {
    get(key, def = null) {
      try { return JSON.parse(localStorage.getItem(key)); } catch { return def; }
    },
    set(key, value) {
      localStorage.setItem(key, JSON.stringify(value));
    },
    push(key, item) {
      const arr = Storage.get(key, []);
      arr.push(item);
      Storage.set(key, arr);
    },
    update(key, updater) {
      const current = Storage.get(key);
      const next = updater(current);
      Storage.set(key, next);
    }
  };
  window.Storage = Storage;
})();