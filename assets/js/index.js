class TableCampaigns {

    state = {
        columns: [],
        campaigns: [],
        pages: 0,
        sortBy: '',
        order: '',
        currentPage: 0
    };

    constructor(data = {}) {
        if (data.length) this.setState(data);
    }

    render() {
        const table = document.createElement('table');
        const thead = document.createElement('thead');
        const tbody = document.createElement('tbody');
        const paginationList = document.createElement('ul');
        paginationList.id = 'foreach-pagination';

        const columns = this.generateTableHeaderColumnsHtml();
        const rows = this.generateRowsHtml();
        const paginationListItems = this.generatePaginationHtml();

        thead.innerHTML = columns.join('');
        tbody.innerHTML = rows.join('');
        paginationList.innerHTML = paginationListItems;

        table.append(thead);
        table.append(tbody);

        return {
            table,
            paginationList
        };
    }

    generateTableHeaderColumnsHtml() {
        return this.state.columns.map(({id, title, sortable}) => `
                <th
                    data-sort-by="${id}"
                    data-sortable="${sortable}"
                    class="${id == this.state.sortBy ? 'active' : ''}"
                    ${id == this.state.sortBy ? 'data-order=' + this.state.order : ''}
                >${title}</th>`
        );
    }

    generateRowsHtml() {
        return this.state.campaigns.map(({
            name,
            campaign_edit_link,
            playlist,
            playlist_edit_link,
            client,
            client_edit_link,
            center,
            center_edit_link,
            media,
            media_icon,
            repeats,
            start_date_campaign,
            end_date_campaign,
            report_link
        }) => {

            let html = `<tr>
                <td>
                    <a href="${campaign_edit_link}" title="${name}">${name}</a>
                </td>
                <td>
                    <a href="${playlist_edit_link}" title="${playlist}">${playlist}</a>
                </td>
                <td>
                    <a href="${client_edit_link}" title="${client}">${client}</a>
                </td>
                <td>
                    <a href="${center_edit_link}" title="${center}">${center}</a>
                </td>
                <td>
                    <a href="${media}" title="${name}">${media_icon}</a>
                </td>
                <td>${repeats}</td>
                <td>${start_date_campaign}</td>
                <td>${end_date_campaign}</td>
                <td>
                    <a href="${report_link}" title="${name}" target="_blank">Generate Report</a>
                </td>
            </tr>`;

            return html;
        });
    }

    generatePaginationHtml() {
        let pagesListItems = '';
        for (let i = 0; i < this.state.pages; i++) {
            pagesListItems += `<li data-page-number="${i + 1}" class="${this.state.currentPage == (i + 1) ? 'active' : ''}">${i + 1}</li>`;
        }
        return pagesListItems;
    }

    setState(state) {
        this.state = {...this.state, ...state};
    }
}

(function ($) {
    $(document).ready(function() {

        const tableCampaignWrapper = $('#TableCampaignsWrapper');
        const page = tableCampaignWrapper.data('page-number');
        const sortBy = tableCampaignWrapper.data('sort-by');
        const order = tableCampaignWrapper.data('order');
        const limitCampaign = 10;
        let currentPage = page;
        let currentSortBy = sortBy;
        let currentOrder = order;

        const tableCampaigns = new TableCampaigns({ sortBy, order, currentPage });

        $('body').on('click', '#TableCampaignsWrapper table th', function (event) {
            currentSortBy = $(this).data('sort-by');
            const isSortable = $(this).data('sortable');

            if (!isSortable) return;

            if ($(this).hasClass('active')) {
                currentOrder = currentOrder == 'ASC' ? 'DESC' : 'ASC';
            }

            $('#TableCampaignsWrapper table th').each(item => { $(this).removeClass('active'); });

            $(this).addClass('active');

            getCampaigns(currentPage, limitCampaign, currentSortBy, currentOrder, successGetCampaignsCallback);
        });

        $('body').on('click', '#foreach-pagination li', function (event) {
            const pageNumber = currentPage = $(this).data('page-number');

            getCampaigns(pageNumber, limitCampaign, sortBy, currentOrder, successGetCampaignsCallback);
        });

        getCampaigns(page, limitCampaign, sortBy, currentOrder, successGetCampaignsCallback);

        function successGetCampaignsCallback({ columns, campaigns, pages }) {
            tableCampaigns.setState({
                columns,
                campaigns,
                pages,
                sortBy: currentSortBy,
                order: currentOrder,
                currentPage
            });
            const { table, paginationList } = tableCampaigns.render();

            tableCampaignWrapper.empty();

            tableCampaignWrapper.append(table);
            tableCampaignWrapper.append(paginationList);

            tableCampaignWrapper.data('order', currentOrder);
            tableCampaignWrapper.data('page-number', currentPage);

            modifyUrl('p', currentPage);
            modifyUrl('order', currentOrder);
            modifyUrl('sort-by', currentSortBy);
        }
    });

    function modifyUrl(param, value) {
        const searchParams = new URLSearchParams(window.location.search);
        searchParams.set(param, value);
        const newRelativePathQuery = window.location.pathname + '?' + searchParams.toString();
        history.pushState(null, '', newRelativePathQuery);
    }

    function getCampaigns(
        page = 1,
        limit = 10,
        sortBy = 'name',
        order = 'ASC',
        successCallback) {
        const result = {};

        $.ajax({
            url: campaign_table_line_global.ajax.url,
            type: 'POST',
            data: {
                action: 'get_table_campaigns_columns'
            },
            success: function({success, data} ) {
                if (!success) return;

                const {columns} = data;
                result['columns'] = columns;

                $.ajax({
                    url: campaign_table_line_global.ajax.url,
                    type: 'POST',
                    data: {
                        action: 'get_campaigns',
                        page,
                        limit,
                        sortBy,
                        order
                    },
                    beforeSend: function( xhr ) {

                    },
                    success: function({success, data} ) {
                        if (!success) return;

                        const {campaigns, pages} = data;
                        result['campaigns'] = campaigns;
                        result['pages'] = pages;

                        successCallback(result);
                    }
                });
            }
        });
    }
}(jQuery));
