Introduction
============

In order to submit an annotation project to the [Genomics Education
Partnership](https://thegep.org) (GEP), students must complete the annotation
report form and include three supplemental files (i.e. a GFF file, a transcript
sequence file, and a peptide sequence file). These supplemental files contain
different types of information for all the genes and isoforms that students have
annotated in their projects.

While the [*Gene Model
Checker*](https://gander.wustl.edu/%7ewilson/genechecker/index.html) will
generate these supplemental files for each gene model, these individual files
must be combined into a single project file. To prepare for project submission,
students will need to create a GFF file which contains the GFF entries for all
the genes and isoforms in their project. Similarly, students will need to create
a transcript sequence file which contains the transcript sequences for all the
gene models in their project, and a peptide sequence file which contains all the
protein sequences in their project.

The [*Annotation Files
Merger*](https://gander.wustl.edu/%7ewilson/submissionhelper/index.php) is
designed to help students combine the individual files generated by the *Gene
Model Checker* into a single project file suitable for project submission. This
tool also performs additional checks to verify that all the isoforms have been
annotated and it allows students to view all the annotated gene models on the
*GEP UCSC Genome Browser*. For projects that contain errors in the consensus
sequence, the *Annotation Files Merger* can also combine VCF files generated by
the [*Sequence
Updater*](https://gander.wustl.edu/~wilson/sequence_updater/index.html).

Please see the [*Annotation Files Merger* User
Guide](https://community.gep.wustl.edu/repository/documentations/Annotation_Files_Merger_User_Guide.pdf)
for an overview of the program, and some examples on how to use this program in
practice.



Availability
============

The [*Annotation Files
Merger*](https://gander.wustl.edu/%7ewilson/submissionhelper/index.php) is
available under the "**Resources & Tools**" section of the [F Element project
page](https://thegep.org/felement/) and the [Pathways project
page](https://thegep.org/pathways/) on the GEP website.



External Dependencies
=====================

* A local [mirror of the *UCSC Genome
  Browser*](https://genome.ucsc.edu/goldenPath/help/mirror.html)
* Database for the [*Gene Record
  Finder*](https://gander.wustl.edu/%7ewilson/dmelgenerecord/index.html)
* [*Samtools*](https://www.htslib.org/)
