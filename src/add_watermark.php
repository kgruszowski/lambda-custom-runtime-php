<?php

function add_watermark($data, $sharedRandom)
{
    $s3 = new \Aws\S3\S3Client([
        'region'  => 'eu-central-1',
        'version' => 'latest',
    ]);

    if (!isset($data['Records'])) {
        throw new \Exception("Not a S3 Event");
    }

    $key = $data['Records'][0]['s3']['object']['key'];
    $path = explode('/', $key);

    $result = $s3->getObject([
        'Bucket' => $data['Records'][0]['s3']['bucket']['name'],
        'Key' => $data['Records'][0]['s3']['object']['key'],
        'SaveAs' => '/tmp/'.$path[1]
    ]);

    $image = imagecreatefromjpeg('/tmp/'.$path[1]);
    $watermark = imagecreatefromstring(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAIAAAACACAIAAABMXPacAAAdmklEQVR4nOx9CXhd1XXuWmuf4V7pSrY8yfNs8ITl2Qw2YIoBYwjBhEBCQvrRZngt8F5TGtI073tNm35pXr6UzE1aQnmkIWkmCFMCISUEg5mMDZ7wPOBRsq1Z995z9l7rfWefK1sytiX53Gsj4fXdBH/S1Rn2v/da/17TduDm78E5OXtCZ/sB3u9yDoCzLOcAOMtyDoCzLOcAOMtyDoCzLOcAOMvinO0HOCOC9v8FEJjQgE5HP6EQhQBEEBABmBQYBzQYg6DKU2552vU9JSAi0R9n8/nmfJgNhdETUCzR5QSACMSwIIJEtyERRhFE+xPp8tHeHwAURh9ICI0yHgoxGUThFOcnDe0/ddyQ80YNnDR8wPjB/vCB/QdUVihCikaUBe3wR3+OiCoXmPrmlgOH6nfUZTfvPbR+9+HVm2vfqdcaHYMuk2Ji0jkSYFGCpsvnwvfJTtgOI0SjqrSnGycMyPzJzPMumzVm3rSxQ8pTjhhEjUARSmgXRWHVYAyAFUEwFkqrtwUQHQAyIpsOHlqxdtfv12x/4c0dDYEKISXiIIDBrlfA+wKAeBxQIOPCrReP+NgVM+adP9wBVqiASKIxjnQJRiqJrVISiwMBIncwk5Haib5pNQuKsiChRFqKgTRAQ1v+Cz988scra0NIkWkz2LWC6ZsqCIUUoHGyYBxH0gj5QWn65NIpn7x29oh+meO/jMf+GbGSSOdHkCjUBhwwIVOki9AgkqfAQGRGlAN47I+itRMNZXWmbNyQ/kaOIOcNue9fGyBkNCoKByK0uc6hOxbP/NzNC4ZXOqDc7v29MIKSUEEYgLti7f5V2/bmguyoARWXzDp/7MB+LnBH3JJI3wRAMSjRxmu7YID+xl/cesnkYYZcBnah6ykZjawAic4bers299lv/uyVLfVtbgUQOhor5Pk7r5ty78evTpPVP4lh6JsAGFKu6OVTyu/7nx8cnCEkQtEYKQsFXY2YADCyI8GBLP7pP/x0/WGl3TIE7YSGERvcwV9/Yqumsn/82OXJR79PbcSUiAKH0EmZwDPmjiVjf/D5m4dWOExpAqXQIex69GOV7ggz+j94ZOX6wxiiFmEWCYgMoOhsG/rfeGLN6gMNwoF0R82fUvoOACaaj1rER3D/8qrR/3TH0hQKoHs6axxVFuix51cLndhmBKweeW4Vo7IGmJM8dt8BIKYznmleOm/E//nTK/tT4DkRT8fu6P0OYpkm7z2S3X4k4JP8rSFvxbp9IZLlr4kUUd8BgACY1aSh6tt3XpVxKMSU/VmPByia1KIPNzUZrxzkJFtZVJt2HNCABojl/QxARNwZQaMAo1cpzf/6P5ZWZ1JCngvtFL2nwqABfSBkhRR0/I1g+0e4UbsHG7IAoaBmMO0fFhKhAEhj96xD7wYABZSxuydil4ObF0+ZM61aCSro2glzqsuCVA/oR6ZVWJ3sC4TY1NwioKQAM8Y60BFR2kFGcLplG3o3ACCArASjEe8vzZ+95WJPstaVk4CbICDKoEo1sJ8iOen4CGAuFyCwYk2A8ccANrMH4FBkR7p1t96+D0AmhQpVkPvg4pqxVeWKSRxAcU7bNFroRImZOnHEnjUtcOKBxLwq//sHf1tZmRFwjxIhQt6wt1Er4xoDnIJuLMReDgAyoKYQXJX61DU1KcctOP8T2EW7u0o7aK6ePvKZtRuAqeBxi52kVgTEsP7dVgCoP+FFAuzW6Pd6FSTRyJAAThrqT5kwpLiXvnzBBb6uR2EUtra3OM6f46R3A2DnJhPi0jljUyejjKd5XZgwJLNgXFWkXgpu/XMAnEgQQhBzWc1E0sUCwDr4RVJgll85LwJA5PQIbXekdwOAIgKpDLfWTBiOqlj2LOIziI4i77bLLhiRcRR6iqkb0a3Tkd4NQDwtRwzuX1GmiuWg7ygZ37ntivMVmIhrFVfFtUvvBkAi0iKTRlR4KKUAwEH+5LVzK7AFRZ9TQScSRAEZPqgfiT6Z4yyJsMDI/v6Nl9d43BZpphJI7wbAOmbMkAoCckrxJpEpILp7+fwy0sLnADiJOE5ptAMU3HmTBpffcMlkAl2KG/QFADLl5SW6snWwkQ/mrg9dUimNpbhF7wdAJJvLlfLyBEjnV2euu3BiKa7fuwEQJCFqyBGwMSf3XCaRiFuhmyLnszdeVilZUuhpF6lolLR3A2B3rFLfzFIIfpVKEHHq+MHL5oxW7Go3j6ZoTszeDUAcBNlbdySakJwoON7FjRA95LtuvCit64UYi7faejcAVnDX/kMMikr9KoI1E6sX1wwjreScCopFgEXUztqmg21B4gydLoU8lrtuvjQtYRHNTe8GwIoYdN/cuodL5Cs4ehsAIr5w8rBLplRT8QJZvR8AkRD9Fet2mGTpIV0KIqByy4Dv/vBFftBcrMv2fgBAmNyVb+4oDQvtdKO4yubS6dULzhtUrIv2dgAcAoUQvLmrfl99qykpFY29cag8du+5aV7K5JB9JYaSLbzeDgCDsJAKIPXUy2/HVV1QQhhs5jrCotmTLxhWRk4gBCoZIertANhUTgam1CMrtuQYWDRAULp1YP2jqpzg09fP8Thna2nez6mJETuPNAMbvXrTnm0Hj9gcaSmtKgIhDJddOnV4uU8MWiUaQwXTlhXvyc6GxCk7iCGoQWVy2bSRCD6iYAlZKQKotIK2bOMfNrZxN2pRTyG9fgUcFVbuL55b28YKwXDp34vELL/q4gpqwmSRsj4EANKOI/Lb17ZqCUVK6BeyIqiccYMyH7hwNJlEgZq+AwCy1ug/+NSafKRYS+uWEEADjifmlqvnOBImuVTfAUAIRJznNh5Yu+OIcKJB6VIQmEEzyKLJI2pGlSFpAgNoi7d7KH0HAGAwyKEqf+CxlYI9HogeSmSHEcBHc/MVM5V2GMotIe2x6us7ACCQKCOAv3xpx5a6tlLfzQEkVC6pWy+fUQVZQkWnlbnVdwCI3gWNa7LNzqAHnlgpJfZO2+0GanCGVrhXzx1NEpAY7PnK6zsACBgxKk9OaLL3//eW3U050c0BCEhJ0kkKFcXRUpDli6a5HGgXgXt8r74DQEdpCfHfH3uZyXVBSzdaliQSlIXzJlaXhcgegNfTv+6bAGh0H3p2/YEsdrNUMZlghZJr549VgqcxnH0TABA41AoPPfmKQSdRwV637qV8wasWTFUQCvY4Q6nkAFhyZitJgXwOFTskrmMbCyACKSJFiK4C5Rj2WTxQipGsmxEQHKUQCYkgGkpDYLrDtQV1Tvn//uRbh7KB0aWyAe1vCOjQ5XPGD09lBY5vRtSllL5ID+MWYGJbeKGACCmWtMtZR/KjqzLjhlVMrO43oH9Fv0xZme8TYms+V9fQVHuk8e29TVv2HG7Qyqi0cN44aQF0dL7LEY3B29dG//W7N+5cNr/k7wjoIVw2a9LulXU9RbvkANi8fcGIooFxMn5w5LwB3uWzJ185Y3jN5DHV/dIuaCQ3UhTCUKgHIkElQIZ1IGrD7oOvrtn86Gt7V2062IYZUGkt2a5uSwgcqLL7n3jz49fMryrxtowBfTTXLJj4k5W7NXSvJ1S7lKRnHBbCp1DInWLyJBhbXXbbJROWXDh55rghHnDchjDaOwpQ3C3PrpL2VlRxuS4X3M3R6snXNeQff3nzz1/YuGJzg0afiVDCCNxj/khpvymhGEHXM+E/3zH/7mVz7JqwrZhK4KbWIIqD/W1c85kfHAr89vfuVmiuJPEAQiPR2lII7JMeU+F99gOzvnrHwusvnDJyQMZBjNR69IkQKPwHCoAQHPt3/J34qwqpMl02a8KwD100ZWx1et/++sNNgXW/tCdwdqpjFBuoMQBm594jH7lyekqxgELk02EqXUhE/lHEV/LC+v3battLOboHdGkCMoQAnitcDsFfLJvxw88tW3LBwMryTDLXeaGjpOs5s8dW3Xp1TWVaVq3f34ZeQXe964XRJjY3tbQNH5ieO2EwIWlUpdBGAqgQyOi6Nv37t/ZxhLQhCQS6vltJAHCAXJOfVJG9/ws3fWrxlJTr2n6DlKSMK64BQyuCbgp53vkjp42uWrV6Y0voaPBJOjWOiVdGZEjArztY99FrZkX2HaAEkbL2BUwIbuo/n36VMSURIka6sdqKth7ta7GNyCJJMG9M+c+/9pklk/trR5Fd9oxFC5IghoCuy2rZ7IkP/N3yAW4rESPq9scoKKS4vF2Q3n6n4fk1O1ikRJWOsV+IwZkydvDAlLFzREn3rHHxALDshUQ8gIvG+w9/6aNTBjqgMj4oFc0Nom6sx1Ndv9O/bU8IBaRw7vihD9z7oQHQRuQWhsJaWrsaBMAQhVlMPfT0m1oC5lJtygjAQcwQzBg9lAEV57o5tkUDwL5wxtEwa7T7X1+8fZAfKYQipnGfRNAFvHz64K/+5ZJ00EgIQgqACY41WmIRdv3/fm3TjsMhlabS8agogGkThys0VgOe6YZNqDgYWxH88PM3VZertOsiuKXpb9HhlgBE5BPcunDiHUsm+6bZ9k9ysVOeqLBwC/X7xXOrTYm9EiQ8ecxgVWDOZwSAdrOGwuBL01f/6vrJ/ZXtmeyC0Lu6Fkn7hzv8JIFtwGi2G/DTYj53+1WThqSIGcAl7rjBRBQdkv/LP6wN7QooKQrjRw4Gk+++pU8EQNyxzU43dB24Y8mMZTNHkptWUCDC7yLdEv9PRyZLGMSctC1e918AHSRUqeqM94Xbr/ZMEF2yY2MHJCUGKP/2wbY12/bFnT67P0N7JIw0bsgA3+RFnG5uBJKtACHbeZyVmFHl/HcfvQjBOwX3in+FYJSEEQrR3EcEKspIIOD18yYsmDzE4TajyjrcNUJa0AlV+ZMvb2YRAo0R/MXXjwQysH+ZqwgjxlV6FRRzDkJ2QP7mprkDyx2SaPqd7PuhcM6YPJu86IBJR/YxFJ0zwskbDSBgSsynrpvlUR65Y7dDiQ+zYHCfemV7zp5xAWBKkzkkLnD/ykz3feDJnHGFrDxnRKXz8T+ZbchxDUvnXMmInSJbmox7Dmefe339q1sO7qhtacpyue9UV6XHjRx05dShs6aNrVCgRAMosa6hnvttWEhdu+C8EQMzOw/nTQfWK0jxqSKb99Vv2l9fM7ScSpa5iCAVZWloau3mDRIBEO19It7v/vniCeW+3Xco1R6UOzoF8mzUhv1NX//57x9dsS2n/EB8AU+w3XH76t5v/eK1kf349qtrPrHswoEpcFAsX+yx44wJy1h/4OIp9z25odMUtA1uDXDeSa1Yu33msKkGXKckjjlURL7rFHRtN7RQIhVkIMXglunDNy2p6fwbOdpKUzM//MdNSz//8I9eOdziVeWjbToidEqcyqn0lsbKf/zp+sV//eDzm94JhZGzusclR6jsMS/XLDjfM/kTfoMRX1+3iwuR29KsAelZT/tkiaWRIcP55w0ZN7ji3b/VzCHL95/ZdOf3f7OXy50Q7Ziq6O/oODXVxhRkHWdrvf7Yl37269e35sDreZITkoggzB8/aEj5iYPjjGrdttq8EIMpTbRYRCCX70FgMmFmb0igF885jzoXSR8tUvnj6o1/++CzOV2mAhMqJg6JDTBC50wFEV8iGwEs6cMw4u5/eXTFloM2nUQ6XrJLEWEB8cnMnjzKBjXjQm481toWaOfBpoa2ktUy2TOsbEddLDTTESy41k/ySQQAIzmcXzhl6HEJSVblclM2d/e//b5VMnlFmjRHdlgZxGj+dc7dZIxTO+2ykewhqbjnW082BSbCIJpS0s0enEDKETCUmjuuXAEDISiMjHr8IQYJssrdtnevUzipp9iC0MBQ1xowKBSDSlBZQnryTyIAhDCFwbSJo45bSKglBP39J1ZuO3IaepYZYcv+1vsfX2XfII4jd00WJD4rDMlFmTJ2FLEoe7QRGkCtoo9xkF2G1K79DSZiHyUgosINdc0STTRwjUYjaIT4FJ+ELAhg/PCqCg8Y5Dil3qTl/z2zJSCvp1tdW9vCeSfzwG/Xf3r5JWVkT+TqHmOJfdEOwPgRQ33OazBs6ezRNEVCdAwcrjtkY5bF34oJOO/U1ttYIAuiJyfmAh0fOSEANHJI2ok7y3Z4GU2wcu3uXQ2IXiimZ4uMrK+AEXc25F96c8ufzJxk2ZDp0lwdjSQLwPlDMy987ROiCiEIOabxJRQ9ur+LYGzMpMiCIHPGDX3u67fbZna211BXkggACmnEwAqC45OfFPGLb+zQDkMPR98eTsHWkRfmlPf02l1LasZq8GxEoTtSCAynXJwxsWi11D0QpKqKdFVFuvt/kcwGgFRUlL3bQjLAG5v2MaIkIxtvbTooRCVKZXiPSAIAEJHA8xFQH7fz0CCbDrSKJO3esHVfq47TivpqCmVCFSSFrJDjp3kYmLrWECktyU6yONIcZkPxPLETpbuZNqeWkjR4Lbi34yMr6dhPupYkRlhsn4QAQbzjsm0kkJAcxciKpedlU8eeDsL6Fl1ZpTSKitlUssETGyFSoogSnjLQ+bJ232MiukWF7pbdvnRCZxw2t+YFj+/XFvG+YjQQC4m++avnqjKuQVSFytPTH7SYpLqsb7h4+rSxIxK2GOh85Wizsbsx+9PfrAh6MqcxqQoSaWpts2/SiSY6joOWZyeUnKr83tNbRdnUq8QtyaxjXJWHzfNnTJ4qxp4eXCzTIgL06xUb/unna3NeJUAP1FzC5Fw5cKTx+F0YgOc7lWV+Y2vS11MmJ6gYXOQgUUpPrJwja06O8OQxw9B6Ros2/Mw5oIefXJV3BiL3rEQg4UZMttbltBhXOvMposlD/JU7ALveCp5KTOxG4yA+rT3Bc1qxuXJjh/arzrh29IvEbiO7wo++uHH9QQNuj9uXJpsEbPYfbm3OyXHbJAKZMabK1i6/t/g7QThv6ggFxc3PMo0a7vvZi6FbdhrJfwmD8pCH1Mad+453LIq+ZNoYZYJS14r2VBzOXzFnLIguYkMbjfTwc+vXHRAj5jR6VCQMyjOD++bmvXL8z3HhzPEe5t8rC8A63kl4QLm/sGZCUVJ0LYQR2zvYHN73kz8aSoPkT8O5nGwFoGHClzbsPW7Pq8Ad1q/skulV3cnPLr3Yg/WQPNHXzZ80JOUBpZSt60hyUVvKo9tM+A//8ZvdLT5ITtDhnscYEvqCXBB8ce3Oxs45f4bEleDWxTXOe0EFRXM/VGI8BZ++dgYWtqyY0D4pCUXMr1/f9Z8v7LCN604z1yvhCoimQm2Qen7N1s4/JkT8wMVTJ1aWJB28hyKCpEx+6eyRM8ZWKSzCQfAiohk31uW/8P2nc1DOBKe9nBIaYUFgrcoefX5DNAUKJogty/bLCe66YZYjbIsqzjwSWIjJijC6FRTe8+GFiYl/Ia0RAVo03fW1R/a2+Y4ESQxKMhXEbrQF1LlHXqvdU9+iTZ4tLbZlwaiQPnHl7NnDfFVw05e6i1VHiWPicX4XpVj/2dIZM0cPYFQJuL9wxF+NYZMVuPe7j7y8s0kThyoRo0rGgizvZcKs0Q88sSokQtBHN3eI6Pv+lz91RX/dhtwPS3MIzokfDBjFCHlCLpLMGObd85ErQjIqwTNw3CzfBDnArzz83I9e2hOSR2wS9qJIBoAwoBhFRqn/eHbDoTaNhXxRe2kri6YOv/uDNSnTTHRGGVG8iXY5N4wav/VXN1S5oUNOEu2PVgG1Ueo7j77yzV+9mnMyDBG1TcimEhXpYexdRxTEbCDlYf2imvNFAjq20iMtPHvqqLe27dxW18YCFJ8TmOSRT/U4yp5DaJ8IFAr0k8bv/PXyxVOqkdgBH06nnaUUjkxEzoN865FXv/LjFVm3yvJOBYk9jokAkKM11QKi3HVb9i+ZP6G6wreOS4qfm1GlSS+aO+X1t9btaXTtlIm+Xfy0nGizJSSihEkERVVg/r67lt44f5LvuISuNQg9G/04IYnEMHMO4Ks/evb//vKNZqcKOLDmRCSxQ6+IRXpBo/S799uPZ4VMewALkRzbSWFI2v3J/75t0Xk+oTZUXpJT6VCAAoS89bm5A5zm79551S0XTnASJD+IHX0jubpQ7vrGU19//O08poENF68HURHrhIVJ7W/IiWlbeMGEaKZb3hMXMCmUjCs3XDzp4KHmjbtqGXUR4yGxIBNxisn3MBxfhfd//qZls0crx3UiBnya5gfFaB1uPqLv+OdHnlx7JEc+I0dEg+T0mX9nKRoAtiwpZ5z06vX7LhidmThqsAKO9vtCcQmrQZeQls2dOG5wauWGzW2hGxkQsrUqdsUgFN6rPQ3i5PwOjx7gE7NbilknEfim4SPzhz94z01ThlciOfYM1G6dciixSWsPGosIIgYCv1nzzme+/LN1+zkkh5gBlcSe7CJJ0QCwhkCJcB7VH15fO3/GhJFVfkRRVWT54tFSRC7i+eOGfvCy6c2Nje/s2qXZE5VWki+cjGpbRZCE0Sui1zl78FiKLRUqYE1ENyOcfBfIh3DqIOfbd17zv5Yv6p/2HOWqyNp3P5Amtr0cmsiEGxRTl+MvPfjM3z/0/MEwFaILYJik6J3BS9ItxUMZ6gYPfPGmyyZWK+VApI5I7OlnDhgxoiNYcuv2tHz3ly8/9dKWWuwvYFNxo51Oyua1MBw3zzp4GqOFQrZALdI8Kh02TR+R+bMbF92yaHJ5RPVFyKGeqTgRYHtLQTEG1PPr9vztD55ZW8chmrjCiZhNCUxXSQAATBG0jFT4nXuvu3zq6JQS19ZQG7RNhW3CUJyzlWWoa2p96oXNT7/y9gtvH2hTFWiyQqkwGl1zsqlGSpQJfQn7+2rR7NG3LZl56dQRKWRH+XbHRAawR1174lpZ4jBg2NuQ+5cf/+5HK3a3YD8QzcSAOiJ8rKR43RaOSmn6BZERzrjQUoH6K7cv/PDSuf1Io8G4fMyWqYICjIyEROYYQAegmvK8ZsP217cf2Lizcce+Iwdra2vbnLg/h6U4OQT2HTV40MBpIwdPHVO5YPLQi6dPyHhKQWiLRUgVGt+wdYecmqjEQ0n2y3FZO9dn9fceX/Wvj71xOHBspavYxILIfgm4tvdD8Tsil2YFdJA05G+5aNyX77hySBoc19e2ksB66E7KTNiegWEAcjpsbjFBEE3APOn+ZWVVGccx4qtjZ+z3fFsltrhQrBpDBKPZHMnKQ0+/+m+PvfZOq6PJK1lHiRNIyQEgx1VhOLEy/OKfL11+0fhoSrMhdE6dkxAXrirkQimxjTNzIdsYgXrWFqyTCAsajus+BLfVhw/8dtVPnlm9P+8A+JGel9CUvPX0MSk5AIpd4+QVgKfdK6dm/ua2S+dOGCTol51kfxTbhniHLXwsccEG/gstME5/FxRdkgMwWYaX1+156Nm3nn3l7WZJGVICyOJKoQfXmXPclhwAGwtQEY1Q2jGeR7mrZg79zPXzFk8fQ3EZFcbK4GjXCKFCu6VOB+mrjrklxzWvgaOxKDnWLKjdfWaLDeJWdBiKrNla9+iK1b96cfvueiMuGPFMxAlCu8ZI4GiTujMkJQegsygEdsQQ8KzhqZuvmHnjwinDqxwSTyEgaZtu6hZScUXY5vYWnEYdCD0f87midCjJNRY8FRF5BjYstpMFQGNev7h+97Ord/zhjW07a7OavND2/AMJzuC7n1jOKABUyB9GQhJxlMmWY27qeaOX1gy7aOaEWROqyylQpOxyIBTRRNbDFo91h5gaxqBgTNutZ8lW1kgIqDgiYbC3Pr9m8zurNu9ZtWHfG9ubsgwBESOhiVG1y6MUdXo9lDMKQOF9keyuV1myyMRMoFzODUjJpFFD5o6rHD9q8LiRg4dVDxw9oNJTojDS3KpDuz1mHQ86i+RFGlt17eHGfXUN22sPbz/QumXPkU27D9Y2tkVbNEpZz0GWo8s4UGgMwhGcWDR/ThI50yoI4sPuBAiN7TBnG9fEZD9uekaMLE7ERtiloKI83T+TKS9LZ1IdVBBBPq/zubCxqfVISzZvIIwUj8Noa+PIifYXaCzFt8EBUWBjZGhLZaOJb8uSbfD0LEvpWxd3EnPUjJr2k3bkaHWdVQscDQqGBY3vN7bBnrYcwMkyXhGgrJNPPbqGKeQLHGsBa+KWHfYbDO25Qu8F6bOlP71FzgFwluUcAGdZzgFwluUcAGdZzgFwluUcAGdZ/n8AAAD//09Pvl/CjhzFAAAAAElFTkSuQmCC'));
    imagecopy($image, $watermark, 10, 10, 0, 0, imagesx($watermark), imagesy($watermark));

    ob_start();
    imagejpeg($image);
    $image_contents = ob_get_clean();

    $result = $s3->putObject([
        'ContentType' => $result['ContentType'],
        'Bucket' => $data['Records'][0]['s3']['bucket']['name'],
        'Key' => "watermarked/".$path[1],
        'Body' => $image_contents
    ]);

    print sprintf("Random number initialized in the runtime: %d", $sharedRandom).PHP_EOL;

    return 'Watermark added';
}
