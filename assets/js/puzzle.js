( function () {

    var FILES = 'abcdefgh';
    var PIECES = {
        'K':'♔','Q':'♕','R':'♖','B':'♗','N':'♘','P':'♙',
        'k':'♚','q':'♛','r':'♜','b':'♝','n':'♞','p':'♟'
    };

    /* Parse FEN string into 8×8 array */
    function fenToBoard( fen ) {
        var rows = fen.split(' ')[0].split('/');
        return rows.map( function( row ) {
            var r = [];
            row.split('').forEach( function( ch ) {
                if ( isNaN( ch ) ) { r.push( ch ); }
                else { for ( var i = 0; i < parseInt( ch ); i++ ) r.push(''); }
            });
            return r;
        });
    }

    /* Convert UCI move string (e.g. "e2e4") to rank/file indices */
    function uciToIndices( uci ) {
        return {
            fromFile: FILES.indexOf( uci[0] ),
            fromRank: 8 - parseInt( uci[1] ),
            toFile:   FILES.indexOf( uci[2] ),
            toRank:   8 - parseInt( uci[3] )
        };
    }

    /* Convert UCI to human-readable e.g. "e2→e4" */
    function uciToReadable( uci ) {
        return uci.slice(0,2) + '→' + uci.slice(2,4);
    }

    /* Render an 8×8 board into a container element */
    function renderBoard( container, fen, highlights ) {
        var board = fenToBoard( fen );
        var grid = document.createElement('div');
        grid.className = 'cd-puzzle-grid';

        for ( var r = 0; r < 8; r++ ) {
            for ( var f = 0; f < 8; f++ ) {
                var sq = document.createElement('div');
                var isLight = ( r + f ) % 2 === 0;
                var isHL = highlights && highlights.some( function(h) {
                    return h.r === r && h.f === f;
                });
                sq.className = 'cd-puzzle-sq ' + ( isLight ? 'cd-sq-light' : 'cd-sq-dark' )
                             + ( isHL ? ' cd-sq-hl' : '' );
                var piece = board[r][f];
                if ( piece && PIECES[piece] ) {
                    sq.textContent = PIECES[piece];
                }
                grid.appendChild( sq );
            }
        }
        container.innerHTML = '';
        container.appendChild( grid );
    }

    /* Initialise all puzzle boards on the page */
    function initPuzzles() {
        var boards = document.querySelectorAll('.cd-puzzle-board');
        boards.forEach( function( wrap ) {
            var fen      = wrap.dataset.fen;
            var solution = wrap.dataset.solution.split(',').filter(Boolean);
            var inner    = wrap.querySelector('.cd-puzzle-board-inner');

            if ( ! fen || ! inner ) return;

            renderBoard( inner, fen, [] );

            /* Reveal button */
            var id  = wrap.id.replace('cd-puzzle-board-', '');
            var btn = document.querySelector('.cd-puzzle-reveal[data-id="' + id + '"]');
            var sol = document.getElementById( 'cd-puzzle-sol-' + id );

            if ( ! btn || ! sol ) return;

            btn.addEventListener('click', function() {
                var isVisible = sol.classList.contains('cd-puzzle-sol-visible');
                if ( isVisible ) {
                    sol.classList.remove('cd-puzzle-sol-visible');
                    sol.innerHTML = '';
                    renderBoard( inner, fen, [] );
                    btn.textContent = 'Show solution';
                } else {
                    /* highlight the first move on the board */
                    var firstMove = solution[0];
                    var highlights = [];
                    if ( firstMove && firstMove.length >= 4 ) {
                        var idx = uciToIndices( firstMove );
                        highlights = [
                            { r: idx.fromRank, f: idx.fromFile },
                            { r: idx.toRank,   f: idx.toFile }
                        ];
                    }
                    renderBoard( inner, fen, highlights );

                    /* write solution text */
                    var readableMoves = solution.map( uciToReadable ).join('  ');
                    sol.innerHTML = '<strong>Solution:</strong> <span class="cd-sol-moves">'
                                  + readableMoves + '</span>';
                    sol.classList.add('cd-puzzle-sol-visible');
                    btn.textContent = 'Hide solution';
                }
            });
        });
    }

    if ( document.readyState === 'loading' ) {
        document.addEventListener('DOMContentLoaded', initPuzzles);
    } else {
        initPuzzles();
    }

} )();