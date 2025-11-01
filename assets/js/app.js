(function($) {
  'use strict';

  const App = {
    project: null,
    blocks: [],
    pages: [],
    media: [],
    selectedBlock: null,
    currentLang: 'en',
    editorLang: 'en',
    i18n: {},
    sortable: null,
    autoSaveTimer: null,

    init: function() {
      this.bindEvents();
      this.initModals();
      this.loadI18n();
    },

    loadI18n: function() {
      const lang = localStorage.getItem('editorLang') || 'en';
      this.editorLang = lang;

      $.ajax({
        url: `/i18n/ui.${lang}.json`,
        type: 'GET',
        dataType: 'json',
        cache: true,
        crossDomain: false,
        success: (data) => {
          this.i18n = data;
          this.renderUI();
          this.checkExistingProject();
        },
        error: () => {
          $.ajax({
            url: '/i18n/ui.en.json',
            type: 'GET',
            dataType: 'json',
            cache: true,
            crossDomain: false,
            success: (data) => {
              this.i18n = data;
              this.renderUI();
              this.checkExistingProject();
            },
            error: (xhr, status, error) => {
              console.error('Failed to load i18n files:', status, error);
              this.i18n = {};
              this.renderUI();
              this.checkExistingProject();
            }
          });
        }
      });
    },

    t: function(key) {
      return this.i18n[key] || key;
    },

    bindEvents: function() {
      $(document).on('click', '.menu-toggle', () => this.toggleSidebar());
      $(document).on('click', '.inspector-toggle', () => this.toggleInspector());
      $(document).on('click', '.drawer-overlay', () => this.closeDrawers());
      $(document).on('click', '#create-project-btn', () => this.showCreateProjectModal());
      $(document).on('click', '#export-zip-btn', () => this.exportZip());
      $(document).on('click', '.block-item', (e) => this.selectBlock(e));
      $(document).on('click', '.language-tab', (e) => this.switchLanguage(e));
      $(document).on('click', '.fab', () => this.showAddBlockModal());
      $(document).on('change input', '.inspector-content input, .inspector-content textarea, .inspector-content select', 
        () => this.scheduleAutoSave());
      $(document).on('click', '.add-item-btn', (e) => this.addItem(e));
      $(document).on('click', '.delete-item-btn', (e) => this.deleteItem(e));
    },

    renderUI: function() {
      $('title').text(this.t('app_title'));
      $('.app-title').text(this.t('app_title'));
    },

    checkExistingProject: function() {
      const projectId = localStorage.getItem('currentProjectId');

      if (projectId) {
        this.loadProject(projectId);
      } else {
        this.showCreateProjectModal();
      }
    },

    showCreateProjectModal: function() {
      const html = `
        <div class="modal active" id="create-project-modal">
          <div class="modal-overlay"></div>
          <div class="modal-content">
            <div class="modal-header">${this.t('create_project')}</div>
            <form id="create-project-form">
              <div class="form-group">
                <label class="form-label">${this.t('project_name')}</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="form-group">
                <label class="form-label">${this.t('select_languages')}</label>
                <div class="form-check">
                  <input type="checkbox" name="languages" value="en" class="form-check-input" checked>
                  <label>EN</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" name="languages" value="uk" class="form-check-input">
                  <label>UK</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" name="languages" value="ru" class="form-check-input">
                  <label>RU</label>
                </div>
                <div class="form-check">
                  <input type="checkbox" name="languages" value="pl" class="form-check-input">
                  <label>PL</label>
                </div>
              </div>
              <div class="form-group">
                <label class="form-label">${this.t('select_theme')}</label>
                <select name="theme_preset" class="form-select">
                  <option value="minimal">${this.t('theme_minimal')}</option>
                  <option value="bold">${this.t('theme_bold')}</option>
                  <option value="creative">${this.t('theme_creative')}</option>
                </select>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-primary">${this.t('create_project')}</button>
              </div>
            </form>
          </div>
        </div>
      `;

      $('body').append(html);

      $('#create-project-form').on('submit', (e) => {
        e.preventDefault();
        this.createProject(e.target);
      });
    },

    createProject: function(form) {
      const formData = new FormData(form);
      const languages = [];

      $('input[name="languages"]:checked').each(function() {
        languages.push($(this).val());
      });

      formData.append('action', 'project.create');
      formData.append('languages', JSON.stringify(languages));
      formData.append('csrf_token', this.getCsrfToken());

      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        crossDomain: false,
        success: (response) => {
          localStorage.setItem('currentProjectId', response.id);
          $('#create-project-modal').remove();
          this.loadProject(response.id);
          this.showToast(this.t('saved'));
        },
        error: (xhr, status, error) => {
          console.error('Project creation error:', status, error, xhr);
          let errorMsg = this.t('error');
          if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMsg = xhr.responseJSON.error;
          } else if (status === 'error' && !xhr.status) {
            errorMsg = 'Network error - check if API endpoint is accessible';
          }
          this.showToast(errorMsg, 'danger');
        }
      });
    },

    loadProject: function(id) {
      $.ajax({
        url: '/api.php',
        type: 'GET',
        data: { action: 'project.get', id: id },
        crossDomain: false,
        success: (data) => {
          this.project = data.project;
          this.pages = data.pages;
          this.blocks = data.blocks;
          this.media = data.media;
          this.currentLang = this.project.languages[0];
          
          this.renderProject();
          this.initSortable();
        },
        error: (xhr, status, error) => {
          console.error('Project load error:', status, error, xhr);
          let errorMsg = this.t('error');
          if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMsg = xhr.responseJSON.error;
          } else if (status === 'error' && !xhr.status) {
            errorMsg = 'Network error - cannot load project';
          }
          this.showToast(errorMsg, 'danger');
          localStorage.removeItem('currentProjectId');
          setTimeout(() => this.showCreateProjectModal(), 2000);
        }
      });
    },

    renderProject: function() {
      $('.project-name').text(this.project.name);
      this.renderBlocks();
      this.renderCanvas();
      this.renderLanguageTabs();
    },

    renderBlocks: function() {
      const $list = $('.blocks-list').empty();

      this.blocks.forEach(block => {
        const selected = this.selectedBlock && this.selectedBlock.id === block.id ? 'selected' : '';
        const $item = $(`
          <div class="block-item ${selected}" data-id="${block.id}">
            <div class="block-handle">☰</div>
            <div class="block-info">
              <strong>${this.t('block_' + block.type)}</strong>
            </div>
          </div>
        `);
        $list.append($item);
      });
    },

    renderCanvas: function() {
      const $canvas = $('.canvas-inner').empty();

      this.blocks.forEach(block => {
        const html = this.renderBlockPreview(block);
        $canvas.append(html);
      });
    },

    renderBlockPreview: function(block) {
      const data = block.data;
      const lang = this.currentLang;

      switch (block.type) {
        case 'hero':
          return `
            <section class="preview-hero" style="padding: 4rem 2rem; text-align: center; background: #f8f9fa;">
              <h1>${this.getLocalizedText(data, 'title', lang)}</h1>
              <p>${this.getLocalizedText(data, 'subtitle', lang)}</p>
              <button style="margin-top: 1rem; padding: 0.75rem 2rem; background: #0066cc; color: white; border: none; border-radius: 4px;">
                ${this.getLocalizedText(data, 'ctaText', lang)}
              </button>
            </section>
          `;

        case 'about':
          return `
            <section class="preview-about" style="padding: 4rem 2rem;">
              <h2>${this.getLocalizedText(data, 'headline', lang)}</h2>
              <p>${this.getLocalizedText(data, 'content', lang)}</p>
            </section>
          `;

        case 'projects':
          return `
            <section class="preview-projects" style="padding: 4rem 2rem; background: #f8f9fa;">
              <h2>${this.getLocalizedText(data, 'headline', lang)}</h2>
              <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2rem; margin-top: 2rem;">
                ${(data.items || []).map(item => `
                  <div style="background: white; padding: 1rem; border-radius: 8px;">
                    <h3>${this.getLocalizedText(item, 'title', lang)}</h3>
                    <p>${this.getLocalizedText(item, 'desc', lang)}</p>
                  </div>
                `).join('')}
              </div>
            </section>
          `;

        case 'experience':
          return `
            <section class="preview-experience" style="padding: 4rem 2rem;">
              <h2>${this.getLocalizedText(data, 'headline', lang)}</h2>
            </section>
          `;

        case 'contact':
          return `
            <section class="preview-contact" style="padding: 4rem 2rem; background: #f8f9fa;">
              <h2>${this.getLocalizedText(data, 'headline', lang)}</h2>
              <p>Email: ${data.email || ''}</p>
            </section>
          `;

        case 'footer':
          return `
            <footer class="preview-footer" style="padding: 2rem; text-align: center; background: #343a40; color: white;">
              <p>${this.getLocalizedText(data, 'copyright', lang)}</p>
            </footer>
          `;

        default:
          return '';
      }
    },

    getLocalizedText: function(data, key, lang) {
      if (data && data[lang] && data[lang][key]) {
        return data[lang][key];
      }
      if (data && data.en && data.en[key]) {
        return data.en[key];
      }
      return '';
    },

    renderLanguageTabs: function() {
      const $tabs = $('.language-tabs').empty();

      this.project.languages.forEach(lang => {
        const active = lang === this.currentLang ? 'active' : '';
        $tabs.append(`
          <button class="language-tab ${active}" data-lang="${lang}">${lang.toUpperCase()}</button>
        `);
      });
    },

    switchLanguage: function(e) {
      this.currentLang = $(e.target).data('lang');
      this.renderLanguageTabs();
      this.renderCanvas();
      
      if (this.selectedBlock) {
        this.renderInspector(this.selectedBlock);
      }
    },

    selectBlock: function(e) {
      const id = $(e.currentTarget).data('id');
      this.selectedBlock = this.blocks.find(b => b.id == id);
      
      $('.block-item').removeClass('selected');
      $(e.currentTarget).addClass('selected');
      
      this.renderInspector(this.selectedBlock);
      this.showInspector();
    },

    renderInspector: function(block) {
      const $content = $('.inspector-content').empty();
      
      const html = this.getInspectorFields(block);
      $content.html(html);
    },

    getInspectorFields: function(block) {
      const data = block.data;
      const lang = this.currentLang;
      
      let html = `<div class="form-group">
        <small style="color: #6c757d;">${this.t('language')}: ${lang.toUpperCase()}</small>
      </div>`;

      switch (block.type) {
        case 'hero':
          html += `
            <div class="form-group">
              <label class="form-label">${this.t('title')}</label>
              <input type="text" class="form-control" data-field="title" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'title', lang))}">
            </div>
            <div class="form-group">
              <label class="form-label">${this.t('subtitle')}</label>
              <input type="text" class="form-control" data-field="subtitle" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'subtitle', lang))}">
            </div>
            <div class="form-group">
              <label class="form-label">${this.t('cta_text')}</label>
              <input type="text" class="form-control" data-field="ctaText" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'ctaText', lang))}">
            </div>
            <div class="form-group">
              <label class="form-label">${this.t('cta_link')}</label>
              <input type="text" class="form-control" data-field="ctaHref" 
                value="${data.ctaHref || '#'}">
            </div>
          `;
          break;

        case 'about':
          html += `
            <div class="form-group">
              <label class="form-label">${this.t('headline')}</label>
              <input type="text" class="form-control" data-field="headline" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'headline', lang))}">
            </div>
            <div class="form-group">
              <label class="form-label">${this.t('content_text')}</label>
              <textarea class="form-textarea" data-field="content">${this.escapeHtml(this.getLocalizedText(data, 'content', lang))}</textarea>
            </div>
          `;
          break;

        case 'projects':
          html += `
            <div class="form-group">
              <label class="form-label">${this.t('headline')}</label>
              <input type="text" class="form-control" data-field="headline" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'headline', lang))}">
            </div>
            <div class="form-group">
              <label class="form-label">${this.t('projects_layout')}</label>
              <select class="form-select" data-field="layout">
                <option value="grid-2" ${data.layout === 'grid-2' ? 'selected' : ''}>${this.t('layout_grid_2')}</option>
                <option value="grid-3" ${data.layout === 'grid-3' ? 'selected' : ''}>${this.t('layout_grid_3')}</option>
                <option value="grid-4" ${data.layout === 'grid-4' ? 'selected' : ''}>${this.t('layout_grid_4')}</option>
              </select>
            </div>
          `;
          break;

        case 'experience':
          html += `
            <div class="form-group">
              <label class="form-label">${this.t('headline')}</label>
              <input type="text" class="form-control" data-field="headline" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'headline', lang))}">
            </div>
          `;
          break;

        case 'contact':
          html += `
            <div class="form-group">
              <label class="form-label">${this.t('headline')}</label>
              <input type="text" class="form-control" data-field="headline" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'headline', lang))}">
            </div>
            <div class="form-group">
              <label class="form-label">${this.t('email')}</label>
              <input type="email" class="form-control" data-field="email" 
                value="${data.email || ''}">
            </div>
            <div class="form-group">
              <div class="form-check">
                <input type="checkbox" class="form-check-input" data-field="formEnabled" 
                  ${data.formEnabled ? 'checked' : ''}>
                <label>${this.t('enable_form')}</label>
              </div>
            </div>
          `;
          break;

        case 'footer':
          html += `
            <div class="form-group">
              <label class="form-label">${this.t('copyright')}</label>
              <input type="text" class="form-control" data-field="copyright" 
                value="${this.escapeHtml(this.getLocalizedText(data, 'copyright', lang))}">
            </div>
          `;
          break;
      }

      html += `
        <div class="form-group">
          <button type="button" class="btn btn-danger btn-sm" onclick="App.deleteBlock(${block.id})">
            ${this.t('delete')} ${this.t('block_' + block.type)}
          </button>
        </div>
      `;

      return html;
    },

    scheduleAutoSave: function() {
      if (!this.selectedBlock) return;

      clearTimeout(this.autoSaveTimer);
      
      this.autoSaveTimer = setTimeout(() => {
        this.saveBlockData();
      }, 1000);
    },

    saveBlockData: function() {
      if (!this.selectedBlock) return;

      const data = this.selectedBlock.data;
      const lang = this.currentLang;

      if (!data[lang]) {
        data[lang] = {};
      }

      $('.inspector-content [data-field]').each(function() {
        const $field = $(this);
        const fieldName = $field.data('field');
        const value = $field.is(':checkbox') ? $field.is(':checked') : $field.val();

        if ($field.data('field-lang') === false || ['email', 'ctaHref', 'layout', 'formEnabled'].includes(fieldName)) {
          data[fieldName] = value;
        } else {
          data[lang][fieldName] = value;
        }
      });

      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: {
          action: 'block.update',
          id: this.selectedBlock.id,
          data: JSON.stringify(data),
          csrf_token: this.getCsrfToken()
        },
        crossDomain: false,
        success: () => {
          this.renderCanvas();
          localStorage.setItem('lastSave', Date.now());
        },
        error: (xhr, status, error) => {
          console.error('Block save error:', status, error, xhr);
        }
      });
    },

    deleteBlock: function(id) {
      if (!confirm(this.t('confirm_delete'))) return;

      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: {
          action: 'block.delete',
          id: id,
          csrf_token: this.getCsrfToken()
        },
        crossDomain: false,
        success: () => {
          this.blocks = this.blocks.filter(b => b.id != id);
          this.selectedBlock = null;
          this.renderBlocks();
          this.renderCanvas();
          $('.inspector-content').empty();
          this.showToast(this.t('saved'));
        },
        error: (xhr, status, error) => {
          console.error('Block delete error:', status, error, xhr);
          this.showToast(this.t('error'), 'danger');
        }
      });
    },

    initSortable: function() {
      const listEl = document.querySelector('.blocks-list');
      
      if (!listEl || this.sortable) return;

      this.sortable = Sortable.create(listEl, {
        handle: '.block-handle',
        animation: 150,
        delay: 250,
        delayOnTouchOnly: true,
        onEnd: () => {
          this.saveBlockOrder();
        }
      });
    },

    saveBlockOrder: function() {
      const order = [];
      
      $('.block-item').each(function(index) {
        order.push({
          id: $(this).data('id'),
          order: index
        });
      });

      $.ajax({
        url: '/api.php',
        type: 'POST',
        data: {
          action: 'block.reorder',
          blocks: JSON.stringify(order),
          csrf_token: this.getCsrfToken()
        },
        crossDomain: false,
        success: () => {
          this.blocks.sort((a, b) => {
            const aOrder = order.find(o => o.id == a.id);
            const bOrder = order.find(o => o.id == b.id);
            return (aOrder?.order || 0) - (bOrder?.order || 0);
          });
          this.renderCanvas();
        },
        error: (xhr, status, error) => {
          console.error('Block reorder error:', status, error, xhr);
        }
      });
    },

    exportZip: function() {
      if (!this.project) return;

      this.showToast(this.t('loading'));

      const form = $('<form>', {
        method: 'POST',
        action: '/api.php'
      });

      form.append($('<input>', { type: 'hidden', name: 'action', value: 'export.zip' }));
      form.append($('<input>', { type: 'hidden', name: 'project_id', value: this.project.id }));
      form.append($('<input>', { type: 'hidden', name: 'csrf_token', value: this.getCsrfToken() }));

      $('body').append(form);
      form.submit();
      form.remove();

      setTimeout(() => {
        this.showToast(this.t('export_success'), 'success');
      }, 1000);
    },

    toggleSidebar: function() {
      $('.sidebar').toggleClass('active');
      $('.drawer-overlay').toggleClass('active');
    },

    toggleInspector: function() {
      $('.inspector').toggleClass('active');
    },

    showInspector: function() {
      if (window.innerWidth <= 1024) {
        $('.inspector').addClass('active');
      }
    },

    closeDrawers: function() {
      $('.sidebar').removeClass('active');
      $('.inspector').removeClass('active');
      $('.drawer-overlay').removeClass('active');
    },

    initModals: function() {
      $(document).on('click', '.modal-overlay', function(e) {
        if (e.target === this) {
          $(this).closest('.modal').remove();
        }
      });
    },

    showToast: function(message, type = 'info') {
      const $toast = $('<div class="toast active"></div>').text(message);
      $('body').append($toast);

      setTimeout(() => $toast.addClass('active'), 10);
      setTimeout(() => {
        $toast.removeClass('active');
        setTimeout(() => $toast.remove(), 300);
      }, 3000);
    },

    getCsrfToken: function() {
      let token = $('meta[name="csrf-token"]').attr('content');
      
      if (!token) {
        token = 'dummy-token';
      }
      
      return token;
    },

    escapeHtml: function(text) {
      const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      };
      return String(text || '').replace(/[&<>"']/g, m => map[m]);
    },

    addItem: function(e) {
    },

    deleteItem: function(e) {
    }
  };

  window.App = App;

  $(document).ready(function() {
    App.init();
  });

})(jQuery);
