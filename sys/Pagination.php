<?php 
class Pagination{ 
    private $metodo = ''; 
    private $numRows = 0; 
    private $perPage = 10; 
    private $totalRows = 0;
    private $numLinks = 2; 
    private $currentPage = 0; 
    private $paginate = '';

     
    public function __construct($metodo,$num_rows,$perPage,$numLinks,$currentPage){ 
         $this->metodo = $metodo;
         $this->numRows = $num_rows;
         $this->totalRows = ceil($num_rows/$perPage);
         $this->perPage = $perPage;
         $this->numLinks = $numLinks;
         $this->currentPage = $currentPage;
    } 
       
    public function createLinks(){ 

            $this->paginate .= '
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="'.( $this->currentPage <= 1 ? 'disabled':'').'"><a onclick="'.$this->metodo.'(1)" aria-label="Anterior"><span aria-hidden="true">&laquo;</span></a></li>';
                        for($i = 1; $i <= $this->totalRows; $i++ ){
                            if ($this->totalRows <= $this->numLinks) {
            $this->paginate .= ' <li class="'.($i == $this->currentPage ? 'active':'').'" ><a onclick="'.$this->metodo.'('.$i.')">'.$i.'</a></li>';
                            } else {
                                $start_temp = $this->currentPage - (ceil($this->numLinks/2) - 1);
                                $end_temp = $this->currentPage + (ceil($this->numLinks/2) - 1);
                                if ($i >= $start_temp && $i <= $end_temp) {
            $this->paginate .= '<li class="'.($i == $this->currentPage ? 'active':'').'" ><a onclick="'.$this->metodo.'('.$i.')">'.$i.'</a></li>';
                                }
                            } 
                        }
            $this->paginate .= '<li class="'.( $this->currentPage >= $this->totalRows ? 'disabled':'').'"><a onclick="'.$this->metodo.'('.$this->totalRows.')" aria-label="Siguiente"><span aria-hidden="true">&raquo;</span></a></li>
                    </ul>
                </nav>';
            
            return $this->paginate;
    }
}