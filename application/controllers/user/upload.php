<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once(BASEPATH . '../application/libraries/S3.php');

//AWS access info
if (!defined('awsAccessKey')) define('awsAccessKey', 'AKIAJY42QCXN4NX4SHRQ');
if (!defined('awsSecretKey')) define('awsSecretKey', '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');


class Upload extends CI_Controller {

	public function upload_image()
	{
		$config = array(
            'allowed_types' => 'jpg|jpeg|gif|png',
            'upload_path'   => './temp',
            'max_size'		=> 3072,
            'overwrite'     => true
        );

        $this->load->library('upload', $config);

        $this->upload->overwrite = true;

        $response['responseStatus'] = "Not OK";

        if (!$this->upload->do_upload())
        {
            $response['responseStatus'] = "Your image could not be uploaded";
        }
        else
        {
            $data = $this->upload->data();

            //instantiate the class
            $s3 = new S3(awsAccessKey, awsSecretKey);

            $ext = pathinfo($data['full_path'], PATHINFO_EXTENSION);
            $imgName = ((string)time()).".".$ext;

            $input = S3::inputFile($data['full_path'], FALSE);

            if($s3->putObject(file_get_contents($data['full_path']), "tlahui-content", $imgName, S3::ACL_PUBLIC_READ))
            {
                $response['responseStatus'] = "OK";
                $response['url'] = "https://s3.amazonaws.com/tlahui-content/".$imgName;
                unlink($data['full_path']);
            }
            else
            {
                $response['responseStatus'] = "Your image could not be uploaded";
                unlink($data['full_path']);
            }
        }

        echo json_encode($response);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
