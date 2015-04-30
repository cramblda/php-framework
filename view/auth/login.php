<!-- start page container -->
  <div class="container-fluid">
    <div class="row">

      <!-- Start page content -->
      <div class="col-md-12">


    <?php if ( isset($errors) ) { ?>

        <div class="row">
            <div class="col-md-6 col-md-offset-3 alert alert-danger" role="alert">

                <?php foreach ( $errors as $key => $error ) { ?>
                    <p><?php print  $error;?><p>
                <?php }?>

            </div>
        </div>

     <?php } ?>

        <div class="row">
            <div class="col-md-6 col-md-offset-3">

                <form method="post" action="<?php echo APP_DOC_ROOT; ?>/auth/login">

                    <div class="panel panel-default">

                        <div class="panel-heading">
                            <h3 class="panel-title">App Name Login</h3>
                        </div>

                        <div class="panel-body">
                            <div class="form-group">
                                <label for="userName">Username</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-user"></i></div>
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="userName"
                                        name="userName"
                                        placeholder="Enter Username"
                                        required
                                        oninvalid="this.setCustomValidity('Username is required.')"
                                        onchange="this.setCustomValidity('')"
                                    >
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="password">Password</label>
                                <div class="input-group">
                                    <div class="input-group-addon"><i class="fa fa-key"></i></div>
                                    <input
                                        type="password"
                                        class="form-control"
                                        id="password"
                                        name="password"
                                        placeholder="Password"
                                        required
                                        oninvalid="this.setCustomValidity('Password is required.')"
                                        onchange="this.setCustomValidity('')"
                                    >
                                </div>
                            </div>

                        </div>

                        <div class="panel-footer">
                            <button
                                type="submit"
                                class="btn btn-primary pull-right"
                                id="loginFormButton"
                                name="loginFormButton"
                            >Login</button>
                            <div class="clearfix"></div>
                        </div>

                    </div>

                </form>

            </div>
        </div>

      </div>
      <!-- End page content -->
