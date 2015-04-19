<?php
class PDF extends FPDF
{
	// Page header
	function Header()
	{
	    // Arial bold 15
	    $this->SetFont('Courier','',15);
	    // Move to the right
	    $this->Cell(80);
	    // Title
	    $this->Cell(30,10,'Title',1,0,'C');
	    // Line break
	    $this->Ln(20);
	}

	// Page footer
	function Footer()
	{
	    $this->SetY(-15);
	    // Arial italic 8
	    $this->SetFont('Courier','',8);
	    // Page number
	    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
	}
}
?>
