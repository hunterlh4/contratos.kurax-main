<?php
//echo $sub_sec_id;
if($sub_sec_id){
	if($sub_sec_id=="tiendas"){
	    ?>
	        <!-- CONTENT AREA -->
	    <div class="content container-fluid">

			<!-- PAGE HEADER -->
			<div class="page-header wide">
				<h1 class="page-title"><?php echo $sec_id;?></h1>
				<div class="page-subtitle"><?php echo $sub_sec_id;?></div>
			</div>
			<!-- /PAGE HEADER -->

	        <div class="row">

	        	<div class="col-xs-12">
	        		<div class="col-xs-4">
	        			 <!-- PANEL: Basic Example -->
		                <div class="panel">

		                    <!-- Panel Heading -->
		                    <div class="panel-heading">

		                        <!-- Panel Title -->
		                        <div class="panel-title">Basic Example</div>
		                        <!-- /Panel Title -->

		                    </div>
		                    <!-- /Panel Heading -->

		                    <!-- Panel Body -->
		                    <div class="panel-body">

		                        <form>
		                            <div class="form-group">
		                                <label for="exampleInputEmail1">Email address</label>
		                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
		                            </div>
		                            <div class="form-group">
		                                <label for="exampleInputPassword1">Password</label>
		                                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
		                            </div>
		                            <div class="form-group">
		                                <label for="exampleInputFile">File input</label>
		                                <input type="file" id="exampleInputFile">
		                                <p class="help-block">Example block-level help text here.</p>
		                            </div>
		                            <div class="checkbox">
		                                <label>
		                                    <input type="checkbox"> Check me out
		                                </label>
		                            </div>
		                            <button type="reset" class="btn btn-o btn-default">Reset Form</button>
		                            <button type="submit" class="btn btn-success">Submit Form</button>
		                        </form>

		                    </div>
		                    <!-- /Panel Body -->

		                </div>
		                <!-- /PANEL: Basic Example -->
	        		</div>
	        		<div class="col-xs-4">
	        			 <!-- PANEL: Basic Example -->
		                <div class="panel">

		                    <!-- Panel Heading -->
		                    <div class="panel-heading">

		                        <!-- Panel Title -->
		                        <div class="panel-title">Basic Example</div>
		                        <!-- /Panel Title -->

		                    </div>
		                    <!-- /Panel Heading -->

		                    <!-- Panel Body -->
		                    <div class="panel-body">

		                        <form>
		                            <div class="form-group">
		                                <label for="exampleInputEmail1">Email address</label>
		                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
		                            </div>
		                            <div class="form-group">
		                                <label for="exampleInputPassword1">Password</label>
		                                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
		                            </div>
		                            <div class="form-group">
		                                <label for="exampleInputFile">File input</label>
		                                <input type="file" id="exampleInputFile">
		                                <p class="help-block">Example block-level help text here.</p>
		                            </div>
		                            <div class="checkbox">
		                                <label>
		                                    <input type="checkbox"> Check me out
		                                </label>
		                            </div>
		                            <button type="reset" class="btn btn-o btn-default">Reset Form</button>
		                            <button type="submit" class="btn btn-success">Submit Form</button>
		                        </form>

		                    </div>
		                    <!-- /Panel Body -->

		                </div>
		                <!-- /PANEL: Basic Example -->
	        		</div>
	        		<div class="col-xs-4">
	        			 <!-- PANEL: Basic Example -->
		                <div class="panel">

		                    <!-- Panel Heading -->
		                    <div class="panel-heading">

		                        <!-- Panel Title -->
		                        <div class="panel-title">Basic Example</div>
		                        <!-- /Panel Title -->

		                    </div>
		                    <!-- /Panel Heading -->

		                    <!-- Panel Body -->
		                    <div class="panel-body">

		                        <form>
		                            <div class="form-group">
		                                <label for="exampleInputEmail1">Email address</label>
		                                <input type="email" class="form-control" id="exampleInputEmail1" placeholder="Email">
		                            </div>
		                            <div class="form-group">
		                                <label for="exampleInputPassword1">Password</label>
		                                <input type="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
		                            </div>
		                            <div class="form-group">
		                                <label for="exampleInputFile">File input</label>
		                                <input type="file" id="exampleInputFile">
		                                <p class="help-block">Example block-level help text here.</p>
		                            </div>
		                            <div class="checkbox">
		                                <label>
		                                    <input type="checkbox"> Check me out
		                                </label>
		                            </div>
		                            <button type="reset" class="btn btn-o btn-default">Reset Form</button>
		                            <button type="submit" class="btn btn-success">Submit Form</button>
		                        </form>

		                    </div>
		                    <!-- /Panel Body -->

		                </div>
		                <!-- /PANEL: Basic Example -->
	        		</div>
	        	</div>

	        </div>

	    </div>
	    <!-- /CONTENT AREA -->
	    <?php
	} else {
		include("sec_".$sec_id."_".$sub_sec_id.".php");
	}
}
?>